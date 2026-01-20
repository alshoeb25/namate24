# VS Code Setup for namate24

This directory contains VS Code workspace settings to optimize the development experience for this Laravel + Vue.js project.

## Recommended Extensions

The following extensions are recommended (see `extensions.json`):

### Required
- **GitHub Copilot** (`github.copilot`) - AI pair programming
- **GitHub Copilot Chat** (`github.copilot-chat`) - Interactive AI assistance

### Highly Recommended
- **PHP Intelephense** (`bmewburn.vscode-intelephense-client`) - PHP language support
- **Vue - Official** (`vue.volar`) - Vue 3 support
- **Tailwind CSS IntelliSense** (`bradlc.vscode-tailwindcss`) - Tailwind CSS autocomplete
- **DotEnv** (`mikestead.dotenv`) - .env file syntax highlighting
- **EditorConfig** (`editorconfig.editorconfig`) - Maintain consistent coding styles

## Performance Optimizations

The workspace settings include several optimizations to prevent slow loading and improve responsiveness:

1. **File Exclusions**: Large directories like `vendor/`, `node_modules/`, and `storage/` are excluded from search and file watching
2. **Editor Limits**: Maximum of 10 open editors per group to reduce memory usage
3. **Intelephense Limits**: File size limit of 5MB to prevent indexing issues
4. **GitHub Copilot**: Enabled for all file types with optimized settings

## Debugging

Launch configurations are provided for:
- **PHP (Xdebug)**: Listen on port 9003
- **Chrome**: Launch browser for frontend debugging

## Troubleshooting

### GitHub Copilot Chat Takes Too Long
If you see "Chat took too long to get ready":
1. Ensure you're signed in to GitHub in VS Code
2. Check that both `github.copilot` and `github.copilot-chat` extensions are installed and enabled
3. Reload VS Code window (Cmd/Ctrl + Shift + P → "Reload Window")
4. Check VS Code Developer Tools (Help → Toggle Developer Tools) for errors

### Slow Performance
If VS Code feels slow:
1. Close unused editor tabs
2. Ensure `vendor/` and `node_modules/` are fully excluded from file watching
3. Increase VS Code's memory limit if needed
4. Consider disabling real-time extensions temporarily while editing large files

## Echo/Pusher Configuration

The Echo (real-time notifications) initialization has been optimized with:
- Connection timeouts (30s activity, 10s pong)
- Error handling for connection failures
- Graceful degradation if Pusher is unavailable

This prevents the frontend from hanging if the WebSocket connection is slow or unavailable.
