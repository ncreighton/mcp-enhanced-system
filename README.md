# MCP Enhanced System - Complete Package

## What's Included

### 1. Core Plugin (`mcp-enhanced-system/core/`)
- **mcp-enhanced-system.php** - Main plugin with:
  - Multi-API support (OpenAI, Anthropic, OpenRouter, Replicate)
  - Persistent memory system
  - Core MCP tools
  - Project context management

### 2. Site-Specific Plugins (`mcp-enhanced-system/site-plugins/`)
Each site gets its own plugin extending the core:

| Plugin | Site | Key Tools |
|--------|------|-----------|
| mcp-site-wealthfromai.php | WealthFromAI | Business opportunities, income breakdowns |
| mcp-site-aiinactionhub.php | AI In Action Hub | Case studies, tool deep dives |
| mcp-site-pulsegearreviews.php | Pulse Gear Reviews | Product reviews, comparisons |
| mcp-site-witchcraftforbeginners.php | Witchcraft For Beginners | Spell guides, moon magic |

### 3. Claude Code Loadout System (`claude-code-loadouts/`)
Complete project configuration system:
- PROJECT-LOADOUT-SYSTEM.md - Master template
- sites/wealthfromai/ - Example complete project

## Installation

### Step 1: Install Core Plugin
1. Upload `mcp-enhanced-system/core/mcp-enhanced-system.php` to `/wp-content/plugins/`
2. Activate in WordPress admin
3. Configure API keys in settings or wp-config.php:
   ```php
   define('OPENAI_API_KEY', 'your-key');
   define('ANTHROPIC_API_KEY', 'your-key');
   define('OPENROUTER_API_KEY', 'your-key');
   define('REPLICATE_API_KEY', 'your-key');
   ```

### Step 2: Install Site-Specific Plugin
1. Upload the appropriate site plugin to `/wp-content/plugins/`
2. Activate (only after core plugin is active)

### Step 3: Set Up Claude Code Project
1. Create folder: `C:\Claude Code Projects\{site-name}\`
2. Copy contents from `claude-code-loadouts/sites/{site-name}/`
3. Update `.mcp/mcp.json` with your site's MCP endpoint
4. Run `auto-start-claude.bat`

## Memory System Usage

### Store to Memory
```
mcp_memory_set key="project_state" value="{json}" context="wealthfromai"
```

### Retrieve from Memory
```
mcp_memory_get key="project_state" context="wealthfromai"
```

### Search Memory
```
mcp_memory_search query="decision" context="wealthfromai"
```

## Site-Specific Tools

Each site plugin adds specialized tools. Example for WealthFromAI:

```javascript
// Generate AI business opportunity content
wfai_business_opportunity({
  opportunity_type: "AI agency",
  investment_level: "low",
  skill_required: "intermediate"
})

// Generate income breakdown
wfai_income_breakdown({
  method: "AI content writing",
  experience_level: "beginner",
  time_investment: "20 hours/week"
})
```

## Support

Created for Nick Creighton's 17-site publishing empire.
Part of the Creative Command Center system.
