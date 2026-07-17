# Baboon WP

A modern, high-performance WordPress plugin boilerplate. This repository serves as a GitHub Template to quickly spawn new, clean plugin projects without carrying over commit history or experimental work-in-progress code.

## 🚀 Getting Started

Follow these steps to generate and automatically configure a new plugin based on this toolkit.

### 1. Create a New Repository

Do not clone this repository directly. Instead:

1. Click the green **[Use this template]** button at the top right of this GitHub repository.
2. Select **Create a new repository**.
3. Name your new project and ensure **Include all branches** is **unchecked** (this ensures you only get the clean, production-ready `main` branch).

### 2. Clone Your New Project

Clone your newly created repository to your local WordPress environment (e.g., inside your Laragon `wp-content/plugins` folder):

```bash
git clone https://github.com/your-username/your-new-repo.git
cd your-new-repo
```

### 3. Run the Auto-Setup Script

This boilerplate includes a self-destructing Node.js script that automatically renames all namespaces, text domains, constants, and filenames to match your new project.

In your terminal, run the following command (replace `"Baboon WP"` with your actual plugin name):

```bash
node setup.js "Baboon WP"
```

**What this script does in under a second:**

- Replaces `Baboon WP` with `Baboon WP`
- Replaces `baboon-wp` with `baboon-wp`
- Replaces `BABOON_WP` with `BABOON_WP`
- Replaces `Baboon WP` with `Baboon WP`
- Renames the main file `baboon-wp.php` to `baboon-wp.php`
- Automatically deletes `setup.js` after execution to keep your repository clean.

### 4. Install Dependencies

Once the setup script has finished and deleted itself, install your dependencies to finalize the setup:

```bash
npm install
composer install
```

## 🛠️ Development

You are now ready to activate the plugin in your local environment and start building.
