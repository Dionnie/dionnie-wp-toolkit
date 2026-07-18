const fs = require("fs");
const path = require("path");
const readline = require("readline");

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout,
});

// Helper function to generate all formatting variations
function generateVariations(name) {
  const cleanName = name.trim();
  return {
    name: cleanName,
    slug: cleanName
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/(^-|-$)/g, ""),
    constant: cleanName
      .toUpperCase()
      .replace(/[^A-Z0-9]+/g, "_")
      .replace(/(^_|_$)/g, ""),
    pascalCase: cleanName
      .split(/[^a-zA-Z0-9]+/)
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(""),
  };
}

// Helper to safely escape strings for Regex
function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

// Directories to ignore so we don't corrupt Git or dependencies
const ignoreDirs = [".git", "node_modules", "vendor", ".vscode", ".idea"];

function processDirectory(directory, replacements, currentSlug, newSlug) {
  const files = fs.readdirSync(directory);

  for (const file of files) {
    const fullPath = path.join(directory, file);
    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      if (!ignoreDirs.includes(file)) {
        // Pass the slugs down into nested directories
        processDirectory(fullPath, replacements, currentSlug, newSlug);
      }
    } else {
      // 1. Process text-based files first
      if (/\.(php|js|json|css|scss|md|txt)$/.test(file)) {
        // Skip this setup file so it doesn't overwrite its own code
        if (file === "setup.js") continue;

        let content = fs.readFileSync(fullPath, "utf8");
        let modified = false;

        replacements.forEach(({ find, replace }) => {
          if (find.test(content)) {
            content = content.replace(find, replace);
            modified = true;
          }
        });

        if (modified) {
          fs.writeFileSync(fullPath, content, "utf8");
        }
      }

      // 2. Dynamically rename the main plugin file if it matches the current slug
      if (file === `${currentSlug}.php`) {
        const newFilePath = path.join(directory, `${newSlug}.php`);
        fs.renameSync(fullPath, newFilePath);
        console.log(`\x1b[32mRenamed file:\x1b[0m ${file} -> ${newSlug}.php`);
      }
    }
  }
}

// Start the interactive prompts
rl.question(
  "\x1b[33mEnter the CURRENT plugin name (e.g., DionnieWPToolkit):\x1b[0m ",
  (currentInput) => {
    if (!currentInput.trim()) {
      console.error(
        "\x1b[31m%s\x1b[0m",
        "Error: Current plugin name cannot be empty.",
      );
      rl.close();
      process.exit(1);
    }

    rl.question("\x1b[33mEnter the NEW plugin name:\x1b[0m ", (newInput) => {
      if (!newInput.trim()) {
        console.error(
          "\x1b[31m%s\x1b[0m",
          "Error: New plugin name cannot be empty.",
        );
        rl.close();
        process.exit(1);
      }

      const current = generateVariations(currentInput);
      const replacement = generateVariations(newInput);

      // Force the replacement name to have zero spaces
      const replacementNameNoSpaces = replacement.name.replace(/\s+/g, "");

      // Map the current strings dynamically to the new strings
      const replacements = [
        {
          find: new RegExp(escapeRegExp(current.pascalCase), "g"),
          replace: replacement.pascalCase,
        },
        {
          find: new RegExp(escapeRegExp(current.constant), "g"),
          replace: replacement.constant,
        },
        // Replaces the spaced version with the strict no-space version
        {
          find: new RegExp(escapeRegExp(current.name), "g"),
          replace: replacementNameNoSpaces,
        },
        {
          find: new RegExp(escapeRegExp(current.slug), "g"),
          replace: replacement.slug,
        },
      ];

      console.log(`\n\x1b[36mReplacing across project:\x1b[0m`);
      console.log(`- "${current.pascalCase}" -> "${replacement.pascalCase}"`);
      console.log(`- "${current.constant}" -> "${replacement.constant}"`);
      console.log(
        `- "${current.name}" -> "${replacementNameNoSpaces}" \x1b[33m(Spaces stripped)\x1b[0m`,
      );
      console.log(`- "${current.slug}" -> "${replacement.slug}"\n`);

      // Execute the replacements and file renaming
      processDirectory(__dirname, replacements, current.slug, replacement.slug);

      console.log(
        "\n\x1b[32m%s\x1b[0m",
        "Success! All strings and filenames have been replaced with no spaces allowed.",
        "If using composer, run 'composer dump-autoload' to refresh the autoloader.",
      );
      rl.close();
    });
  },
);
