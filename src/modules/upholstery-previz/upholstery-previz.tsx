import { createRoot } from "react-dom/client";

import UpholsteryCustomizer from "./previz";

const rootElement = document.getElementById("upholstery-previz-root");
if (rootElement) {
  const root = createRoot(rootElement);
  root.render(<UpholsteryCustomizer />);
}
