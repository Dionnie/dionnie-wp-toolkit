# Specification: Interactive Upholstery Customizer

## 1. Project Overview & Tech Stack

- **Objective:** A single-page interactive web application allowing users to upload furniture photos, apply fabric textures, and preview the results before submitting an inquiry.
- **Tech Stack:** React, Tailwind CSS, Lucide React (Icons).
- **Approach:** For Preview suggest best AI API, use dummy for now but make everything actual implementation

## 2. Core User Flow

1. **Image Ingestion:** User uploads a photo of their sofa/chair via a drag-and-drop area.
2. **Texture Mapping:** User clicks a fabric swatch from a sidebar or grid panel.
3. **Visual Feedback:** The main display blends the texture onto the furniture image instantly.
4. **Conversion:** User either hits "Download Preview" or "Proceed to Inquiry" which opens a form with the `Texture ID` pre-selected.

## 5. Design & Aesthetic Constraints

- Style completely with **Tailwind CSS**.
- Keep the aesthetic highly professional, clean, and minimalist (using white, off-white, and zinc/slate grays for the interface to let the fabric textures stand out).

## 6. Expected Output

- Provide a single, complete component file (`upholstery-previz.tsx`) or a small set of clean, modular components that can be dropped straight into a React project.
