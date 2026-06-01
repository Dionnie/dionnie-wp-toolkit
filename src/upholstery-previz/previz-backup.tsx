import { GoogleGenAI } from "@google/genai";
import {
  Check,
  Download,
  ImageIcon,
  Loader2,
  MessageSquare,
  Palette,
  UploadCloud,
  X,
} from "lucide-react";
import React, { useCallback, useEffect, useRef, useState } from "react";

interface Texture {
  id: string;
  name: string;
  url: string;
}

function UpholsteryCustomizer() {
  const [textures, setTextures] = useState<Texture[]>([]);
  const [isLoadingTextures, setIsLoadingTextures] = useState(true);
  const [uploadedImage, setUploadedImage] = useState<string | null>(null);
  const [selectedTexture, setSelectedTexture] = useState<Texture | null>(null);
  const [previewTexture, setPreviewTexture] = useState<Texture | null>(null);
  const [previewImage, setPreviewImage] = useState<string | null>(null);
  const [isGenerating, setIsGenerating] = useState(false);

  // Inquiry Form State
  const [showInquiryForm, setShowInquiryForm] = useState(false);
  const [formData, setFormData] = useState({ name: "", email: "", notes: "" });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Fetch textures from WordPress REST API
  useEffect(() => {
    const fetchTextures = async () => {
      setIsLoadingTextures(true);
      try {
        // The ?_embed parameter includes the featured media data in the response
        const response = await fetch(
          `${window.location.origin}/wp-json/wp/v2/texture?_embed`,
        );
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        const fetchedTextures: Texture[] = data
          .map((item: any) => {
            // Extract the featured image URL from the _embedded object
            const imageUrl =
              item._embedded?.["wp:featuredmedia"]?.[0]?.source_url || "";

            return {
              id: item.id.toString(),
              name: item.title?.rendered || "Unnamed Texture",
              url: imageUrl,
            };
          })
          .filter((tex: Texture) => tex.url !== ""); // Only keep textures with an image

        setTextures(fetchedTextures);
      } catch (error) {
        console.error("Failed to fetch textures:", error);
      } finally {
        setIsLoadingTextures(false);
      }
    };

    fetchTextures();
  }, []);

  // Handle Drag & Drop Upload
  const onDragOver = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
  }, []);

  const onDrop = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    const files = e.dataTransfer.files;
    const file = files?.[0];
    if (file) {
      handleFile(file);
    }
  }, []);

  const handleFile = (file: File) => {
    if (!file.type.startsWith("image/")) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      setUploadedImage(e.target?.result as string);
      setSelectedTexture(null);
      setPreviewTexture(null);
      setPreviewImage(null);
    };
    reader.readAsDataURL(file);
  };

  // AI Processing Simulation
  const applyTexture = async (texture: Texture) => {
    setSelectedTexture(texture);
    setIsGenerating(true);
    setPreviewImage(null); // Clear previous preview

    try {
      if (!uploadedImage || !texture) return;

      const base64Image = uploadedImage.split(",")[1] || "";
      const mimeType =
        uploadedImage.split(";")[0]?.split(":")?.[1] || "image/jpeg";

      const ai = new GoogleGenAI({
        apiKey: "AIzaSyBLh-gkGSBGOBRGpGurjJAWr57uTUnNeG8",
      });

      const prompt = [
        {
          text: `Re-upholster the furniture using ${texture.name} fabric. Highly detailed, photorealistic.`,
        },
        {
          inlineData: {
            mimeType: mimeType,
            data: base64Image as string,
          },
        },
      ];

      const response = await ai.models.generateContent({
        model: "gemini-2.5-flash-image",
        contents: prompt,
      });

      if (
        response.candidates &&
        response.candidates.length > 0 &&
        response.candidates[0] &&
        response.candidates[0].content &&
        response.candidates[0].content.parts
      ) {
        for (const part of response.candidates[0].content.parts) {
          if (part.inlineData) {
            const generatedImageBase64 = part.inlineData.data;
            const generatedMime = part.inlineData.mimeType || "image/jpeg";
            setPreviewImage(
              `data:${generatedMime};base64,${generatedImageBase64}`,
            );
            setPreviewTexture(texture);
            break;
          }
        }
      } else {
        console.error("Gemini API Error:", response);
        alert("Failed to generate image. Check the console for details.");
      }
    } catch (error) {
      console.error("Fetch Error:", error);
      alert("An error occurred while communicating with the AI.");
    } finally {
      setIsGenerating(false);
    }
  };

  // Dummy Download
  const handleDownload = () => {
    if (!uploadedImage) return;
    const a = document.createElement("a");
    a.href = previewImage || uploadedImage;
    a.download = `preview-${selectedTexture?.name || "custom"}.jpg`;
    a.click();
  };

  const handleInquirySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    // Simulate API submission
    setTimeout(() => {
      setIsSubmitting(false);
      setSubmitted(true);
      setTimeout(() => {
        setShowInquiryForm(false);
        setSubmitted(false);
        setFormData({ name: "", email: "", notes: "" });
      }, 2000);
    }, 1000);
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 p-4 md:p-8 font-sans flex items-center justify-center">
      <div className="w-full max-w-6xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col md:flex-row min-h-[75vh]">
        {/* Canvas / Preview Area */}
        <div className="flex-1 bg-zinc-50 border-r border-slate-100 p-8 flex flex-col items-center justify-center relative min-h-[400px]">
          {!uploadedImage ? (
            <div
              className="w-full max-w-md aspect-square border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center bg-white hover:bg-slate-50 transition-colors cursor-pointer"
              onDragOver={onDragOver}
              onDrop={onDrop}
              onClick={() => fileInputRef.current?.click()}
            >
              <div className="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <UploadCloud className="text-slate-500 w-8 h-8" />
              </div>
              <p className="text-slate-700 font-medium mb-1">
                Click or drag image here
              </p>
              <p className="text-slate-400 text-sm">
                Upload a photo of your furniture
              </p>
              <input
                type="file"
                ref={fileInputRef}
                onChange={(e) => {
                  const files = e.target.files;
                  const file = files?.[0];
                  if (file) {
                    handleFile(file);
                  }
                }}
                className="hidden"
                accept="image/*"
              />
            </div>
          ) : (
            <div className="w-full max-w-xl flex flex-col items-center">
              <div className="relative w-full aspect-square rounded-xl overflow-hidden shadow-lg border border-slate-200 bg-white">
                <img
                  src={previewImage || uploadedImage}
                  alt="Furniture"
                  className="w-full h-full object-contain"
                />

                {/* Dummy Texture Blend Overlay (Simulates AI until implemented) */}
                {!previewImage && previewTexture && (
                  <div
                    className="absolute inset-0 mix-blend-multiply opacity-60 transition-opacity duration-700 bg-cover bg-center"
                    style={{ backgroundImage: `url(${previewTexture.url})` }}
                  />
                )}

                {/* Loading Overlay */}
                {isGenerating && (
                  <div className="absolute inset-0 bg-white/60 backdrop-blur-sm flex flex-col items-center justify-center transition-all">
                    <Loader2 className="w-10 h-10 text-slate-800 animate-spin mb-3" />
                    <p className="text-slate-800 font-medium tracking-wide text-sm">
                      Applying {selectedTexture?.name}...
                    </p>
                  </div>
                )}
              </div>

              <button
                onClick={() => setUploadedImage(null)}
                className="mt-6 text-sm text-slate-500 hover:text-slate-800 flex items-center gap-2 transition-colors"
              >
                <ImageIcon className="w-4 h-4" /> Try another photo
              </button>
            </div>
          )}
        </div>

        {/* Controls Sidebar */}
        <div className="w-full md:w-[400px] bg-white p-8 flex flex-col h-full">
          <div className="mb-8">
            <h2 className="text-2xl font-semibold tracking-tight text-slate-900 mb-2">
              Upholstery Studio
            </h2>
            <p className="text-slate-500 text-sm">
              Select a fabric to preview on your furniture instantly.
            </p>
          </div>

          <div className="flex-1 flex flex-col">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
              <Palette className="w-4 h-4" /> Fabrics
            </h3>

            {isLoadingTextures ? (
              <div className="flex items-center justify-center p-8 mb-8">
                <Loader2 className="w-6 h-6 text-slate-400 animate-spin" />
              </div>
            ) : textures.length > 0 ? (
              <div className="grid grid-cols-3 gap-4 mb-8">
                {textures.map((tex) => (
                  <button
                    key={tex.id}
                    disabled={!uploadedImage || isGenerating}
                    onClick={() => applyTexture(tex)}
                    className={`group relative flex flex-col items-center gap-2 focus:outline-none ${!uploadedImage ? "opacity-50 cursor-not-allowed" : ""}`}
                  >
                    <div className="relative overflow-hidden">
                      <img
                        src={tex.url}
                        alt={tex.name}
                        className={`aspect-square object-cover shadow-inner border-2 transition-all duration-200 ${selectedTexture?.id === tex.id ? "border-slate-800 scale-110" : "border-transparent hover:scale-150"}`}
                      />
                      {selectedTexture?.id === tex.id && (
                        <div className="absolute inset-0 flex items-center justify-center bg-black/20 rounded-full scale-110">
                          <Check className="text-white w-6 h-6 drop-shadow-md" />
                        </div>
                      )}
                    </div>
                    <span
                      className={`text-[11px] text-center font-medium leading-tight ${selectedTexture?.id === tex.id ? "text-slate-900" : "text-slate-500 group-hover:text-slate-700"}`}
                      dangerouslySetInnerHTML={{ __html: tex.name }}
                    />
                  </button>
                ))}
              </div>
            ) : (
              <p className="text-sm text-slate-500 mb-8">No fabrics found.</p>
            )}
          </div>

          {/* Actions */}
          <div className="pt-6 border-t border-slate-100 flex flex-col gap-3">
            <button
              disabled={!previewTexture || isGenerating}
              onClick={handleDownload}
              className="w-full flex items-center justify-center gap-2 py-3 px-4 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Download className="w-4 h-4" /> Download Preview
            </button>
            <button
              disabled={!previewTexture || isGenerating}
              onClick={() => setShowInquiryForm(true)}
              className="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 text-white rounded-lg hover:bg-slate-800 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-slate-200"
            >
              <MessageSquare className="w-4 h-4" /> Proceed to Inquiry
            </button>
          </div>
        </div>
      </div>

      {/* Inquiry Form Modal */}
      {showInquiryForm && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative animate-in fade-in zoom-in duration-200">
            <button
              onClick={() => setShowInquiryForm(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            {submitted ? (
              <div className="p-10 flex flex-col items-center justify-center text-center">
                <div className="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                  <Check className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-semibold text-slate-900 mb-2">
                  Inquiry Sent!
                </h3>
                <p className="text-slate-500">
                  We've received your request for the {selectedTexture?.name}{" "}
                  finish. Our team will contact you shortly.
                </p>
              </div>
            ) : (
              <form onSubmit={handleInquirySubmit} className="p-8">
                <h3 className="text-xl font-semibold text-slate-900 mb-6">
                  Request a Quote
                </h3>

                <div className="mb-6 p-4 bg-slate-50 rounded-lg flex items-center gap-4 border border-slate-100">
                  <div
                    className="w-10 h-10 rounded-full shadow-sm border border-slate-200 bg-cover bg-center"
                    style={{ backgroundImage: `url(${selectedTexture?.url})` }}
                  />
                  <div>
                    <p className="text-xs text-slate-400 uppercase tracking-wider font-semibold">
                      Selected Fabric
                    </p>
                    <p className="text-slate-800 font-medium">
                      {selectedTexture?.name}
                    </p>
                  </div>
                </div>

                <div className="space-y-4">
                  <div>
                    <label
                      htmlFor="name"
                      className="block text-sm font-medium text-slate-700 mb-1"
                    >
                      Full Name
                    </label>
                    <input
                      id="name"
                      type="text"
                      required
                      value={formData.name}
                      onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                      }
                      className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all"
                      placeholder="Jane Doe"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="email"
                      className="block text-sm font-medium text-slate-700 mb-1"
                    >
                      Email Address
                    </label>
                    <input
                      id="email"
                      type="email"
                      required
                      value={formData.email}
                      onChange={(e) =>
                        setFormData({ ...formData, email: e.target.value })
                      }
                      className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all"
                      placeholder="jane@example.com"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="notes"
                      className="block text-sm font-medium text-slate-700 mb-1"
                    >
                      Additional Notes
                    </label>
                    <textarea
                      id="notes"
                      rows={3}
                      value={formData.notes}
                      onChange={(e) =>
                        setFormData({ ...formData, notes: e.target.value })
                      }
                      className="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all resize-none"
                      placeholder="Tell us about dimensions, constraints, etc."
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="w-full mt-8 flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 text-white rounded-lg hover:bg-slate-800 font-medium transition-colors disabled:opacity-70"
                >
                  {isSubmitting ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" /> Submitting...
                    </>
                  ) : (
                    "Submit Inquiry"
                  )}
                </button>
              </form>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

export default UpholsteryCustomizer;
