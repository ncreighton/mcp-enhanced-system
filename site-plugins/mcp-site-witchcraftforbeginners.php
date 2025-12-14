<?php
/**
 * Plugin Name: MCP Site Extension - Witchcraft For Beginners
 * Description: Site-specific MCP tools for authentic witchcraft and spirituality content.
 * Version: 1.0.0
 * Author: Nick Creighton
 * Requires Plugins: mcp-enhanced-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MCP_Site_WitchcraftForBeginners {

    private static $instance = null;
    private $site_key = 'witchcraftforbeginners';
    private $amazon_tag = 'witchcraftforbeginners-20';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'mwai_mcp_tools', [ $this, 'register_tools' ] );
        add_filter( 'mwai_mcp_callback', [ $this, 'handle_callbacks' ], 10, 3 );
    }

    public function register_tools( $tools ) {
        // Spell Guide Generator
        $tools[] = [
            'name'        => 'wfb_spell_guide',
            'description' => 'Generate authentic, safe spell or ritual guide for beginners.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'spell_type'    => [ 'type' => 'string', 'description' => 'Type of spell (protection, prosperity, love, healing, etc.)' ],
                    'difficulty'    => [ 'type' => 'string', 'enum' => [ 'beginner', 'intermediate', 'advanced' ], 'default' => 'beginner' ],
                    'tradition'     => [ 'type' => 'string', 'description' => 'Tradition or path (eclectic, Wiccan, green witch, etc.)' ],
                    'materials'     => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Materials to include/feature' ],
                    'moon_phase'    => [ 'type' => 'string', 'description' => 'Moon phase association' ],
                ],
                'required'   => [ 'spell_type' ],
            ],
        ];

        // Correspondences Guide
        $tools[] = [
            'name'        => 'wfb_correspondences',
            'description' => 'Generate comprehensive correspondences guide for magical practice.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'topic'       => [ 'type' => 'string', 'description' => 'Topic (herb, crystal, planet, element, color, etc.)' ],
                    'item'        => [ 'type' => 'string', 'description' => 'Specific item to cover' ],
                    'depth'       => [ 'type' => 'string', 'enum' => [ 'quick', 'standard', 'comprehensive' ], 'default' => 'standard' ],
                ],
                'required'   => [ 'topic', 'item' ],
            ],
        ];

        // Moon Magic Content
        $tools[] = [
            'name'        => 'wfb_moon_magic',
            'description' => 'Generate moon magic content for specific phases or events.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'moon_phase'   => [ 'type' => 'string', 'description' => 'Moon phase (new, waxing, full, waning, etc.)' ],
                    'zodiac_sign'  => [ 'type' => 'string', 'description' => 'Zodiac sign the moon is in (optional)' ],
                    'content_type' => [ 'type' => 'string', 'enum' => [ 'guide', 'ritual', 'journal_prompts', 'affirmations' ] ],
                ],
                'required'   => [ 'moon_phase' ],
            ],
        ];

        // Sabbat Guide
        $tools[] = [
            'name'        => 'wfb_sabbat_guide',
            'description' => 'Generate comprehensive sabbat/holiday celebration guide.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'sabbat'          => [ 'type' => 'string', 'description' => 'Sabbat name (Samhain, Yule, Imbolc, etc.)' ],
                    'include_ritual'  => [ 'type' => 'boolean', 'default' => true ],
                    'include_recipes' => [ 'type' => 'boolean', 'default' => true ],
                    'tradition'       => [ 'type' => 'string', 'description' => 'Tradition focus' ],
                ],
                'required'   => [ 'sabbat' ],
            ],
        ];

        // Beginner Path Guide
        $tools[] = [
            'name'        => 'wfb_path_guide',
            'description' => 'Generate guide for a specific witchcraft path or tradition.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'path'         => [ 'type' => 'string', 'description' => 'Path or tradition (green witch, kitchen witch, hedge witch, etc.)' ],
                    'focus'        => [ 'type' => 'string', 'description' => 'Specific focus area' ],
                    'beginner_tips' => [ 'type' => 'boolean', 'default' => true ],
                ],
                'required'   => [ 'path' ],
            ],
        ];

        // Tool Consecration Guide
        $tools[] = [
            'name'        => 'wfb_tool_guide',
            'description' => 'Generate guide for magical tools, including consecration and use.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'tool'          => [ 'type' => 'string', 'description' => 'Magical tool (athame, wand, cauldron, etc.)' ],
                    'include_diy'   => [ 'type' => 'boolean', 'default' => true ],
                    'include_consecration' => [ 'type' => 'boolean', 'default' => true ],
                ],
                'required'   => [ 'tool' ],
            ],
        ];

        // Deity Introduction
        $tools[] = [
            'name'        => 'wfb_deity_guide',
            'description' => 'Generate respectful introduction to a deity or pantheon.',
            'category'    => 'Witchcraft For Beginners',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'deity'        => [ 'type' => 'string', 'description' => 'Deity name' ],
                    'pantheon'     => [ 'type' => 'string', 'description' => 'Pantheon or culture' ],
                    'include_offerings' => [ 'type' => 'boolean', 'default' => true ],
                ],
                'required'   => [ 'deity' ],
            ],
        ];

        return $tools;
    }

    public function handle_callbacks( $result, $tool, $args ) {
        switch ( $tool ) {
            case 'wfb_spell_guide':
                return $this->generate_spell_guide( $args );
            case 'wfb_correspondences':
                return $this->generate_correspondences( $args );
            case 'wfb_moon_magic':
                return $this->generate_moon_magic( $args );
            case 'wfb_sabbat_guide':
                return $this->generate_sabbat_guide( $args );
            case 'wfb_path_guide':
                return $this->generate_path_guide( $args );
            case 'wfb_tool_guide':
                return $this->generate_tool_guide( $args );
            case 'wfb_deity_guide':
                return $this->generate_deity_guide( $args );
            default:
                return $result;
        }
    }

    private function generate_spell_guide( $args ) {
        $type = $args['spell_type'] ?? '';
        $difficulty = $args['difficulty'] ?? 'beginner';
        $tradition = $args['tradition'] ?? 'eclectic';
        $materials = $args['materials'] ?? [];
        $moon = $args['moon_phase'] ?? '';

        $prompt = "Create an authentic, safe spell guide for: {$type} ({$difficulty} level). ";
        $prompt .= "Tradition: {$tradition}. ";
        if ( ! empty( $materials ) ) $prompt .= "Include these materials: " . implode( ', ', $materials ) . ". ";
        if ( $moon ) $prompt .= "Moon phase: {$moon}. ";
        $prompt .= "Include: Intention Setting, Materials Needed, Step-by-Step Instructions, ";
        $prompt .= "Tips for Success, Variations, Ethical Considerations. ";
        $prompt .= "Be respectful, safe, and accessible. No closed practice content. Include fire safety.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an experienced witch who has practiced for decades. Write with warmth, wisdom, and reverence. Never include anything from closed practices without proper attribution. Prioritize safety. Use mystical but accessible language. Never promise guaranteed results - magic works with energy and intention.",
            'max_tokens' => 3500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_correspondences( $args ) {
        $topic = $args['topic'] ?? '';
        $item = $args['item'] ?? '';
        $depth = $args['depth'] ?? 'standard';

        $prompt = "Create a {$depth} correspondences guide for {$item} ({$topic}). ";
        $prompt .= "Include: Magical Properties, Element, Planet, Zodiac, Chakra (if applicable), ";
        $prompt .= "Deities Associated, Best Uses in Magic, How to Work With It, Substitutes. ";
        $prompt .= "Be accurate and cite traditional sources where applicable.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an herbalist and magical practitioner with deep knowledge of correspondences. Provide accurate, well-researched information.",
            'max_tokens' => 2500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_moon_magic( $args ) {
        $phase = $args['moon_phase'] ?? '';
        $zodiac = $args['zodiac_sign'] ?? '';
        $type = $args['content_type'] ?? 'guide';

        $prompt = "Create {$type} content for {$phase} moon";
        if ( $zodiac ) $prompt .= " in {$zodiac}";
        $prompt .= ". Include: Energetic Meaning, Best Activities, ";
        
        if ( $type === 'ritual' ) {
            $prompt .= "Complete Ritual with Invocation, Steps, Closing. ";
        } elseif ( $type === 'journal_prompts' ) {
            $prompt .= "10 Deep Reflection Journal Prompts. ";
        } elseif ( $type === 'affirmations' ) {
            $prompt .= "15 Powerful Affirmations. ";
        } else {
            $prompt .= "Spell Suggestions, Practical Tips. ";
        }

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a moon magic practitioner. Write with wonder and reverence for lunar cycles.",
            'max_tokens' => 2500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_sabbat_guide( $args ) {
        $sabbat = $args['sabbat'] ?? '';
        $include_ritual = $args['include_ritual'] ?? true;
        $include_recipes = $args['include_recipes'] ?? true;
        $tradition = $args['tradition'] ?? '';

        $prompt = "Create comprehensive guide for {$sabbat}. ";
        if ( $tradition ) $prompt .= "From {$tradition} perspective. ";
        $prompt .= "Include: History & Meaning, Correspondences, Altar Suggestions, Activities, ";
        if ( $include_ritual ) $prompt .= "Complete Sabbat Ritual, ";
        if ( $include_recipes ) $prompt .= "Traditional Recipes (2-3), ";
        $prompt .= "Crafts, Solitary & Group Options. Be historically accurate and respectful.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a practitioner who honors the Wheel of the Year. Write with seasonal awareness and historical accuracy.",
            'max_tokens' => 4500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_path_guide( $args ) {
        $path = $args['path'] ?? '';
        $focus = $args['focus'] ?? '';
        $beginner_tips = $args['beginner_tips'] ?? true;

        $prompt = "Create comprehensive guide to the {$path} path. ";
        if ( $focus ) $prompt .= "Focus on: {$focus}. ";
        $prompt .= "Include: What is {$path}, Core Practices, Tools & Supplies, ";
        $prompt .= "Getting Started, Daily Practice Ideas, Resources, Common Questions. ";
        if ( $beginner_tips ) $prompt .= "Include beginner-specific advice. ";
        $prompt .= "Be welcoming and encouraging.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an experienced practitioner who remembers what it was like to be new. Guide with patience and wisdom.",
            'max_tokens' => 3500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_tool_guide( $args ) {
        $tool = $args['tool'] ?? '';
        $include_diy = $args['include_diy'] ?? true;
        $include_consecration = $args['include_consecration'] ?? true;

        $prompt = "Create comprehensive guide to the {$tool}. ";
        $prompt .= "Include: History & Symbolism, How to Choose One, How to Use It, ";
        $prompt .= "Correspondences, Care & Storage, ";
        if ( $include_diy ) $prompt .= "DIY Option, ";
        if ( $include_consecration ) $prompt .= "Complete Consecration Ritual. ";
        $prompt .= "Be practical and respectful of tradition.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a witch with extensive experience with magical tools. Provide practical, tradition-informed guidance.",
            'max_tokens' => 3000,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_deity_guide( $args ) {
        $deity = $args['deity'] ?? '';
        $pantheon = $args['pantheon'] ?? '';
        $include_offerings = $args['include_offerings'] ?? true;

        $prompt = "Create respectful introduction to {$deity}";
        if ( $pantheon ) $prompt .= " of the {$pantheon} pantheon";
        $prompt .= ". Include: Who They Are, Mythology & Stories, Symbols & Correspondences, ";
        $prompt .= "Signs of Their Presence, How to Approach Them Respectfully, ";
        if ( $include_offerings ) $prompt .= "Traditional Offerings, ";
        $prompt .= "Prayer/Invocation Example. ";
        $prompt .= "Be historically accurate and culturally respectful. Note if practice is closed.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a scholar of mythology and deity work. Be accurate, respectful, and note when practices may be closed or require initiation.",
            'max_tokens' => 3500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }
}

add_action( 'plugins_loaded', function() {
    if ( class_exists( 'MCP_Enhanced_System' ) ) {
        MCP_Site_WitchcraftForBeginners::get_instance();
    }
}, 20 );
