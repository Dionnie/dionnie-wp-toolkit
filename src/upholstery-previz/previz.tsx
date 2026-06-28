import { GoogleGenAI } from "@google/genai";
import {
  ArrowLeft,
  ArrowRight,
  Check,
  Download,
  Loader2,
  MessageSquare,
  Sparkles,
  UploadCloud,
  X,
} from "lucide-react";
import React, { useCallback, useEffect, useRef, useState } from "react";

interface Texture {
  id: string;
  name: string;
  url: string;
}

// Switch to toggle between real and mock AI for development
const USE_MOCK_AI = true;

function UpholsteryCustomizer() {
  const [textures, setTextures] = useState<Texture[]>([]);
  const [isLoadingTextures, setIsLoadingTextures] = useState(true);
  const [uploadedImage, setUploadedImage] = useState<string | null>(null);
  const [selectedTexture, setSelectedTexture] = useState<Texture | null>(null);
  const [previewImage, setPreviewImage] = useState<string | null>(null);
  const [isGenerating, setIsGenerating] = useState(false);
  const [currentStep, setCurrentStep] = useState<number>(1);

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
      setPreviewImage(null);
      setCurrentStep(2);
    };
    reader.readAsDataURL(file);
  };

  // Mock AI Previsualization for UI/UX development
  const generateMockAIPreview = async () => {
    setIsGenerating(true);
    setPreviewImage(null); // Clear previous preview

    try {
      if (!uploadedImage || !selectedTexture) {
        // Even in mock mode, we need inputs to "generate" from
        setIsGenerating(false);
        return;
      }

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1500));

      // Use the selected texture as a placeholder for the generated image.
      // This allows UI/UX work to proceed without actual AI calls.
      // For a more realistic mock, you could overlay the texture on the uploaded image
      // using a canvas, or use a pre-generated placeholder image string from a separate file.
      setPreviewImage(selectedTexture.url);
    } catch (error) {
      console.error("Mock AI Error:", error);
      alert("An error occurred during the mock AI generation.");
    } finally {
      setIsGenerating(false);
    }
  };

  // AI Previsualization as a "Bonus Feature"
  const generateAIPreview = async () => {
    setIsGenerating(true);
    setPreviewImage(null); // Clear previous preview

    try {
      if (!uploadedImage || !selectedTexture) return;

      const base64Image = uploadedImage.split(",")[1] || "";
      const mimeType =
        uploadedImage.split(";")[0]?.split(":")?.[1] || "image/jpeg";

      const ai = new GoogleGenAI({
        apiKey: "AIzaSyBLh-gkGSBGOBRGpGurjJAWr57uTUnNeG8",
      });

      const prompt = [
        {
          text: `Re-upholster the furniture using ${selectedTexture.name} fabric. Highly detailed, photorealistic.`,
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
      <div className="w-full max-w-5xl bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden flex flex-col min-h-[75vh]">
        {/* Wizard Header */}
        <div className="bg-slate-50 border-b border-slate-100 p-6 md:px-12">
          <div className="mb-8 text-center">
            <h2 className="text-3xl font-bold tracking-tight text-slate-900 mb-2">
              Upholstery Studio
            </h2>
            <p className="text-slate-500 text-lg">
              Visualize new fabrics on your furniture in three easy steps.
            </p>
          </div>

          {/* Progress Bar */}
          <div className="w-full max-w-2xl mx-auto relative">
            <div className="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-200 rounded-full z-0"></div>
            <div
              className="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-indigo-600 rounded-full z-0 transition-all duration-500 ease-in-out"
              style={{ width: `${(currentStep - 1) * 50}%` }}
            ></div>

            <div className="flex justify-between relative z-10">
              {[1, 2, 3].map((step) => (
                <div key={step} className="flex flex-col items-center">
                  <div
                    className={`w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mb-2 transition-colors duration-300 ${
                      currentStep >= step
                        ? "bg-indigo-600 text-white shadow-md shadow-indigo-200"
                        : "bg-white text-slate-400 border-4 border-slate-100"
                    }`}
                  >
                    {step}
                  </div>
                  <span
                    className={`text-sm font-semibold transition-colors duration-300 ${
                      currentStep >= step ? "text-indigo-700" : "text-slate-400"
                    }`}
                  >
                    {step === 1 ? "Photo" : step === 2 ? "Fabric" : "Result"}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Wizard Content */}
        <div className="flex-1 p-6 md:p-12 flex flex-col">
          {currentStep === 1 && (
            <div className="flex-1 flex flex-col items-center justify-center animate-in fade-in zoom-in-95 duration-300">
              {!uploadedImage ? (
                <>
                  <div
                    className="w-full max-w-xl aspect-video md:aspect-[21/9] border-4 border-dashed border-indigo-100 hover:border-indigo-300 hover:bg-indigo-50/50 rounded-3xl flex flex-col items-center justify-center bg-white transition-all cursor-pointer group"
                    onDragOver={onDragOver}
                    onDrop={onDrop}
                    onClick={() => fileInputRef.current?.click()}
                  >
                    <div className="h-24 w-24 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                      <UploadCloud className="w-12 h-12" />
                    </div>
                    <h3 className="text-2xl text-slate-800 font-bold mb-2">
                      This is Lans!!!
                    </h3>
                    <p className="text-slate-500 text-lg">
                      Click to browse or drag and drop an image here
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
                  <button
                    onClick={() => setCurrentStep(2)}
                    className="mt-6 text-slate-500 hover:text-slate-800 font-semibold text-lg underline decoration-2 underline-offset-4 transition-colors"
                  >
                    Skip for now, I just want to browse fabrics
                  </button>
                </>
              ) : (
                <div className="w-full max-w-xl flex flex-col items-center">
                  <img
                    src={uploadedImage}
                    alt="Uploaded Furniture"
                    className="w-full h-auto max-h-[50vh] object-contain rounded-2xl shadow-md border border-slate-200"
                  />
                  <div className="mt-6 flex flex-col sm:flex-row gap-4 w-full">
                    <button
                      onClick={() => fileInputRef.current?.click()}
                      className="flex-1 py-3 px-4 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200 transition-colors text-center"
                    >
                      Replace Image
                    </button>
                    <button
                      onClick={() => setCurrentStep(2)}
                      className="flex-1 py-3 px-4 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-center flex items-center justify-center gap-2"
                    >
                      Continue <ArrowRight className="w-5 h-5" />
                    </button>
                  </div>
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
              )}
            </div>
          )}

          {currentStep === 2 && (
            <div className="flex-1 flex flex-col animate-in fade-in slide-in-from-right-8 duration-300">
              <div className="mb-6">
                <h3 className="text-2xl font-bold text-slate-800 mb-2">
                  Select a Fabric
                </h3>
                <p className="text-slate-500 text-lg">
                  Choose the pattern you want to use.
                </p>
              </div>

              {isLoadingTextures ? (
                <div className="flex-1 flex items-center justify-center">
                  <Loader2 className="w-12 h-12 text-indigo-600 animate-spin" />
                </div>
              ) : textures.length > 0 ? (
                <div className="grid grid-cols-3 sm:grid-cols-4 gap-4 overflow-y-auto pr-2 pb-4 max-h-[40vh] md:max-h-[50vh]">
                  {textures.map((tex) => (
                    <button
                      key={tex.id}
                      onClick={() => {
                        setSelectedTexture(tex);
                        setPreviewImage(null);
                      }}
                      className={`group flex flex-col items-center gap-3 p-3 rounded-xl border-2 transition-all focus:outline-none ${
                        selectedTexture?.id === tex.id
                          ? "border-indigo-600 bg-indigo-50 shadow-sm"
                          : "border-transparent hover:border-slate-200 hover:bg-slate-50"
                      }`}
                    >
                      <div className="relative w-full aspect-square rounded-lg overflow-hidden shadow-sm">
                        <img
                          src={tex.url}
                          alt={tex.name}
                          className={`w-full h-full object-cover transition-transform duration-300 ${
                            selectedTexture?.id === tex.id
                              ? "scale-110"
                              : "group-hover:scale-110"
                          }`}
                        />
                        {selectedTexture?.id === tex.id && (
                          <div className="absolute inset-0 bg-indigo-600/20 flex items-center justify-center">
                            <div className="bg-indigo-600 rounded-full p-1 shadow-md">
                              <Check className="text-white w-5 h-5" />
                            </div>
                          </div>
                        )}
                      </div>
                      <span
                        className={`text-xs text-center font-semibold leading-tight ${
                          selectedTexture?.id === tex.id
                            ? "text-indigo-900"
                            : "text-slate-600"
                        }`}
                        dangerouslySetInnerHTML={{ __html: tex.name }}
                      />
                    </button>
                  ))}
                </div>
              ) : (
                <p className="text-slate-500">No fabrics found.</p>
              )}

              <div className="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
                <button
                  onClick={() => setCurrentStep(1)}
                  className="flex items-center gap-2 px-6 py-4 text-indigo-600 hover:bg-indigo-50 rounded-xl text-lg font-bold transition-all"
                >
                  <ArrowLeft className="w-5 h-5" /> Photo
                </button>
                <button
                  disabled={!selectedTexture}
                  onClick={() => setCurrentStep(3)}
                  className="flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white rounded-xl text-lg font-bold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-indigo-600/20"
                >
                  Next Step <ArrowRight className="w-5 h-5" />
                </button>
              </div>
            </div>
          )}

          {currentStep === 3 && (
            <div className="flex-1 flex flex-col lg:flex-row gap-12 animate-in fade-in slide-in-from-right-8 duration-300">
              <div className="flex-[1.5] flex flex-col items-center justify-center relative">
                <div className="w-full rounded-2xl overflow-hidden border-2 border-slate-100 shadow-md bg-white relative min-h-[300px] flex items-center justify-center p-4">
                  {previewImage ? (
                    <img
                      src={previewImage}
                      alt="AI Generated Preview"
                      className="w-full h-auto max-h-[60vh] object-contain rounded-xl"
                    />
                  ) : (
                    <div className="flex flex-col sm:flex-row items-center justify-center gap-6 w-full">
                      {uploadedImage && (
                        <div className="flex flex-col items-center gap-2 w-full sm:w-1/2 ">
                          <span className="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                            Your Photo
                          </span>
                          <img
                            src={uploadedImage}
                            alt="Uploaded"
                            className="w-full h-auto max-h-[40vh] aspect-square object-cover rounded-xl border border-slate-200 shadow-sm "
                          />
                        </div>
                      )}
                      <div
                        className={`flex flex-col items-center gap-2 ${uploadedImage ? "w-full sm:w-1/2" : "w-full max-w-md"}`}
                      >
                        <span className="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                          Selected Fabric
                        </span>
                        <img
                          src={selectedTexture?.url}
                          alt={selectedTexture?.name}
                          className="w-full h-auto max-h-[40vh] aspect-square object-cover rounded-xl border border-slate-200 shadow-sm"
                        />
                      </div>
                    </div>
                  )}

                  {isGenerating && (
                    <div className="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-10 p-8 text-center rounded-2xl">
                      <div className="relative mb-6">
                        <div className="w-20 h-20 border-4 border-indigo-100 rounded-full"></div>
                        <div className="w-20 h-20 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin absolute inset-0"></div>
                        <Sparkles className="absolute inset-0 m-auto text-indigo-600 w-8 h-8 animate-pulse" />
                      </div>
                      <h3 className="text-2xl font-bold text-slate-800 mb-2">
                        Creating your preview
                      </h3>
                      <p className="text-slate-600 text-lg max-w-xs mx-auto">
                        Please wait while our smart tool carefully applies{" "}
                        {selectedTexture?.name} to your photo...
                      </p>
                    </div>
                  )}
                </div>
              </div>

              <div className="flex-1 flex flex-col justify-center">
                <div className="flex flex-col gap-5 animate-in fade-in slide-in-from-bottom-4">
                  <div className="mb-2">
                    <h2 className="text-3xl font-bold text-slate-800 mb-2">
                      {previewImage ? "Beautiful choice!" : "Ready to inquire?"}
                    </h2>
                    <p className="text-slate-600 text-lg">
                      {previewImage
                        ? `Here is how ${selectedTexture?.name} might look on your furniture.`
                        : `You've selected ${selectedTexture?.name}. Request a quote to get started!`}
                    </p>
                  </div>

                  <button
                    onClick={() => setShowInquiryForm(true)}
                    className="w-full py-5 bg-slate-900 text-white rounded-xl text-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-900/20 transition-all flex items-center justify-center gap-3 transform hover:-translate-y-1"
                  >
                    <MessageSquare className="w-6 h-6" /> Request a Quote
                  </button>

                  {uploadedImage && !previewImage && !isGenerating && (
                    <div className="relative my-4">
                      <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-slate-200"></div>
                      </div>
                      <div className="relative flex justify-center text-sm">
                        <span className="px-4 bg-white text-slate-400">
                          Optional
                        </span>
                      </div>
                    </div>
                  )}

                  {uploadedImage && !previewImage && !isGenerating && (
                    <div className="flex flex-col gap-2">
                      <button
                        onClick={
                          USE_MOCK_AI
                            ? generateMockAIPreview
                            : generateAIPreview
                        }
                        className="w-full py-4 bg-indigo-50 text-indigo-600 border-2 border-indigo-100 rounded-xl text-lg font-bold hover:bg-indigo-100 transition-all flex items-center justify-center gap-2"
                      >
                        <Sparkles className="w-5 h-5" /> Preview with AI
                      </button>
                      <p className="text-sm text-slate-500 text-center px-2">
                        Wondering how it will look? Let our AI magically apply
                        the fabric to your photo for a realistic preview.
                      </p>
                    </div>
                  )}

                  {previewImage && !isGenerating && (
                    <button
                      onClick={handleDownload}
                      className="w-full py-4 bg-white border-2 border-slate-200 text-slate-700 rounded-xl text-lg font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2"
                    >
                      <Download className="w-5 h-5" /> Save Preview Image
                    </button>
                  )}

                  <div className="grid grid-cols-2 gap-4 mt-4 pt-6 border-t border-slate-100">
                    <button
                      onClick={() => {
                        setPreviewImage(null);
                        setCurrentStep(2);
                      }}
                      className="py-3 px-4 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors text-center"
                    >
                      Change fabric
                    </button>
                    <button
                      onClick={() => {
                        setUploadedImage(null);
                        setPreviewImage(null);
                        setSelectedTexture(null);
                        setCurrentStep(1);
                      }}
                      className="py-3 px-4 bg-slate-100 text-slate-700 font-semibold rounded-lg hover:bg-slate-200 transition-colors text-center"
                    >
                      Start over
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}
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
