<?php
/**
 * Plugin Name: MCP Site Extension - Wealth From AI
 * Description: Site-specific MCP tools for AI business and income generation content.
 * Version: 1.0.0
 * Author: Nick Creighton
 * Requires Plugins: mcp-enhanced-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MCP_Site_WealthFromAI {

    private static $instance = null;
    private $site_key = 'wealthfromai';

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
        // AI Business Opportunity Generator
        $tools[] = [
            'name'        => 'wfai_business_opportunity',
            'description' => 'Generate comprehensive content about an AI business opportunity with realistic expectations.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'opportunity_type'  => [ 'type' => 'string', 'description' => 'Type of AI business (e.g., AI agency, freelancing, SaaS)' ],
                    'investment_level'  => [ 'type' => 'string', 'enum' => [ 'free', 'low', 'medium', 'high' ], 'default' => 'low' ],
                    'skill_required'    => [ 'type' => 'string', 'enum' => [ 'none', 'basic', 'intermediate', 'advanced' ], 'default' => 'basic' ],
                    'income_potential'  => [ 'type' => 'string', 'description' => 'Expected income range' ],
                    'time_commitment'   => [ 'type' => 'string', 'description' => 'Hours per week required' ],
                ],
                'required'   => [ 'opportunity_type' ],
            ],
        ];

        // AI Automation Strategy Builder
        $tools[] = [
            'name'        => 'wfai_automation_strategy',
            'description' => 'Create a detailed AI automation strategy for business income.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'business_type'     => [ 'type' => 'string', 'description' => 'Type of business to automate' ],
                    'current_processes' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Current manual processes' ],
                    'goals'             => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Automation goals' ],
                    'budget'            => [ 'type' => 'string', 'description' => 'Budget for automation tools' ],
                ],
                'required'   => [ 'business_type' ],
            ],
        ];

        // AI Success Case Study Generator
        $tools[] = [
            'name'        => 'wfai_case_study',
            'description' => 'Generate an AI success case study with realistic metrics.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'subject_type'    => [ 'type' => 'string', 'description' => 'Type of case study subject (entrepreneur, business, freelancer)' ],
                    'industry'        => [ 'type' => 'string', 'description' => 'Industry or niche' ],
                    'ai_tools_used'   => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'AI tools involved' ],
                    'outcome_metrics' => [ 'type' => 'string', 'description' => 'Key success metrics to highlight' ],
                ],
                'required'   => [ 'subject_type' ],
            ],
        ];

        // AI Tool Monetization Guide
        $tools[] = [
            'name'        => 'wfai_tool_monetization',
            'description' => 'Create a guide on monetizing a specific AI tool.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'tool_name'            => [ 'type' => 'string', 'description' => 'Name of the AI tool' ],
                    'monetization_methods' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Methods to cover' ],
                    'target_market'        => [ 'type' => 'string', 'description' => 'Target customer market' ],
                ],
                'required'   => [ 'tool_name' ],
            ],
        ];

        // Income Breakdown Calculator
        $tools[] = [
            'name'        => 'wfai_income_breakdown',
            'description' => 'Generate realistic income breakdown for an AI income method.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'method'           => [ 'type' => 'string', 'description' => 'Income method to analyze' ],
                    'experience_level' => [ 'type' => 'string', 'enum' => [ 'beginner', 'intermediate', 'advanced' ], 'default' => 'beginner' ],
                    'time_investment'  => [ 'type' => 'string', 'description' => 'Weekly time investment' ],
                ],
                'required'   => [ 'method' ],
            ],
        ];

        // AI Tool Comparison Generator
        $tools[] = [
            'name'        => 'wfai_tool_comparison',
            'description' => 'Generate a comparison of AI tools for business use.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'tools'         => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Tools to compare' ],
                    'use_case'      => [ 'type' => 'string', 'description' => 'Primary use case' ],
                    'criteria'      => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Comparison criteria' ],
                    'budget_range'  => [ 'type' => 'string', 'description' => 'Budget consideration' ],
                ],
                'required'   => [ 'tools', 'use_case' ],
            ],
        ];

        // AI Prompt Engineering Guide
        $tools[] = [
            'name'        => 'wfai_prompt_guide',
            'description' => 'Generate a prompt engineering guide for monetization.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'ai_model'    => [ 'type' => 'string', 'description' => 'AI model (ChatGPT, Claude, etc.)' ],
                    'use_case'    => [ 'type' => 'string', 'description' => 'Business use case' ],
                    'output_type' => [ 'type' => 'string', 'description' => 'Type of output needed' ],
                ],
                'required'   => [ 'ai_model', 'use_case' ],
            ],
        ];

        // AI Industry Report Generator
        $tools[] = [
            'name'        => 'wfai_industry_report',
            'description' => 'Generate an AI industry report or market analysis.',
            'category'    => 'Wealth From AI',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'sector'      => [ 'type' => 'string', 'description' => 'AI sector to analyze' ],
                    'timeframe'   => [ 'type' => 'string', 'description' => 'Report timeframe (e.g., Q4 2024, 2025 outlook)' ],
                    'focus_areas' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Specific focus areas' ],
                ],
                'required'   => [ 'sector' ],
            ],
        ];

        return $tools;
    }

    public function handle_callbacks( $result, $tool, $args ) {
        switch ( $tool ) {
            case 'wfai_business_opportunity':
                return $this->generate_opportunity( $args );
            case 'wfai_automation_strategy':
                return $this->create_automation_strategy( $args );
            case 'wfai_case_study':
                return $this->generate_case_study( $args );
            case 'wfai_tool_monetization':
                return $this->create_monetization_guide( $args );
            case 'wfai_income_breakdown':
                return $this->create_income_breakdown( $args );
            case 'wfai_tool_comparison':
                return $this->generate_tool_comparison( $args );
            case 'wfai_prompt_guide':
                return $this->generate_prompt_guide( $args );
            case 'wfai_industry_report':
                return $this->generate_industry_report( $args );
            default:
                return $result;
        }
    }

    private function generate_opportunity( $args ) {
        $type = $args['opportunity_type'] ?? '';
        $investment = $args['investment_level'] ?? 'low';
        $skill = $args['skill_required'] ?? 'basic';
        $income = $args['income_potential'] ?? '';
        $time = $args['time_commitment'] ?? '';

        $prompt = "Write a detailed guide about this AI business opportunity: {$type}. ";
        $prompt .= "Investment level: {$investment}. Skills required: {$skill}. ";
        if ( $income ) {
            $prompt .= "Income potential: {$income}. ";
        }
        if ( $time ) {
            $prompt .= "Time commitment: {$time}. ";
        }
        $prompt .= "Include: Overview, How It Works, Getting Started Steps, Realistic Income Expectations, ";
        $prompt .= "Required Tools, Common Mistakes, Success Tips. ";
        $prompt .= "Be realistic and honest - no get-rich-quick promises. Focus on sustainable strategies.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a strategic business advisor specializing in AI monetization. Be ambitious but realistic. Always include proof points and actionable steps. Never make unrealistic income promises.",
            'max_tokens' => 3500,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content'          => $response['content'] ?? '',
                'opportunity_type' => $type,
            ],
        ];
    }

    private function create_automation_strategy( $args ) {
        $business = $args['business_type'] ?? '';
        $processes = $args['current_processes'] ?? [];
        $goals = $args['goals'] ?? [];
        $budget = $args['budget'] ?? '';

        $prompt = "Create a comprehensive AI automation strategy for: {$business}. ";
        if ( ! empty( $processes ) ) {
            $prompt .= "Current manual processes: " . implode( ', ', $processes ) . ". ";
        }
        if ( ! empty( $goals ) ) {
            $prompt .= "Automation goals: " . implode( ', ', $goals ) . ". ";
        }
        if ( $budget ) {
            $prompt .= "Budget: {$budget}. ";
        }
        $prompt .= "Include: Current State Analysis, AI Tools Recommendations, Implementation Roadmap, ";
        $prompt .= "ROI Projections, Risk Assessment, Timeline. ";
        $prompt .= "Be specific about tools and realistic about timelines.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an AI automation consultant. Provide practical, actionable strategies with specific tool recommendations.",
            'max_tokens' => 4000,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content'       => $response['content'] ?? '',
                'business_type' => $business,
            ],
        ];
    }

    private function generate_case_study( $args ) {
        $subject = $args['subject_type'] ?? '';
        $industry = $args['industry'] ?? '';
        $tools = $args['ai_tools_used'] ?? [];
        $metrics = $args['outcome_metrics'] ?? '';

        $prompt = "Write a detailed AI success case study about: {$subject}. ";
        if ( $industry ) {
            $prompt .= "Industry: {$industry}. ";
        }
        if ( ! empty( $tools ) ) {
            $prompt .= "AI tools used: " . implode( ', ', $tools ) . ". ";
        }
        if ( $metrics ) {
            $prompt .= "Key metrics: {$metrics}. ";
        }
        $prompt .= "Structure: Challenge, AI Solution, Implementation, Results, Key Takeaways. ";
        $prompt .= "Make it inspiring but believable with specific metrics where possible.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a business journalist documenting AI success stories. Be detailed and credible.",
            'max_tokens' => 3000,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content' => $response['content'] ?? '',
                'subject' => $subject,
            ],
        ];
    }

    private function create_monetization_guide( $args ) {
        $tool = $args['tool_name'] ?? '';
        $methods = $args['monetization_methods'] ?? [];
        $market = $args['target_market'] ?? '';

        $prompt = "Create a comprehensive guide on monetizing {$tool}. ";
        if ( ! empty( $methods ) ) {
            $prompt .= "Monetization methods to cover: " . implode( ', ', $methods ) . ". ";
        }
        if ( $market ) {
            $prompt .= "Target market: {$market}. ";
        }
        $prompt .= "Include: Getting Started, Service Offerings, Pricing Strategies, Finding Clients, ";
        $prompt .= "Scaling the Business, Income Examples. Be actionable and specific.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an entrepreneur who has successfully monetized AI tools. Share practical strategies.",
            'max_tokens' => 3500,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content'   => $response['content'] ?? '',
                'tool_name' => $tool,
            ],
        ];
    }

    private function create_income_breakdown( $args ) {
        $method = $args['method'] ?? '';
        $level = $args['experience_level'] ?? 'beginner';
        $time = $args['time_investment'] ?? '';

        $prompt = "Create a realistic income breakdown for: {$method}. ";
        $prompt .= "Experience level: {$level}. ";
        if ( $time ) {
            $prompt .= "Time investment: {$time}. ";
        }
        $prompt .= "Include: Monthly Income Ranges by Level, Time to First Income, Scaling Path, ";
        $prompt .= "Common Expenses, Net Income Expectations. Be conservative and honest.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a financial advisor for digital entrepreneurs. Never inflate expectations. Always provide conservative estimates.",
            'max_tokens' => 2500,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content' => $response['content'] ?? '',
                'method'  => $method,
            ],
        ];
    }

    private function generate_tool_comparison( $args ) {
        $tools = $args['tools'] ?? [];
        $use_case = $args['use_case'] ?? '';
        $criteria = $args['criteria'] ?? [ 'pricing', 'features', 'ease of use', 'output quality' ];
        $budget = $args['budget_range'] ?? '';

        $prompt = "Create a detailed comparison of these AI tools: " . implode( ' vs ', $tools ) . ". ";
        $prompt .= "For this use case: {$use_case}. ";
        $prompt .= "Comparison criteria: " . implode( ', ', $criteria ) . ". ";
        if ( $budget ) {
            $prompt .= "Budget consideration: {$budget}. ";
        }
        $prompt .= "Include: Overview of each tool, Feature comparison table, Pricing breakdown, ";
        $prompt .= "Best for scenarios, Recommendation. Be objective and thorough.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an AI tools analyst. Provide unbiased, thorough comparisons based on real features and pricing.",
            'max_tokens' => 3500,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content'  => $response['content'] ?? '',
                'tools'    => $tools,
                'use_case' => $use_case,
            ],
        ];
    }

    private function generate_prompt_guide( $args ) {
        $model = $args['ai_model'] ?? '';
        $use_case = $args['use_case'] ?? '';
        $output_type = $args['output_type'] ?? '';

        $prompt = "Create a comprehensive prompt engineering guide for {$model}. ";
        $prompt .= "Use case: {$use_case}. ";
        if ( $output_type ) {
            $prompt .= "Output type: {$output_type}. ";
        }
        $prompt .= "Include: Prompt Structure, Best Practices, Example Prompts, Common Mistakes, ";
        $prompt .= "Advanced Techniques, Templates for different scenarios. Make it actionable.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a prompt engineering expert. Provide practical, tested techniques with real examples.",
            'max_tokens' => 3500,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content' => $response['content'] ?? '',
                'model'   => $model,
            ],
        ];
    }

    private function generate_industry_report( $args ) {
        $sector = $args['sector'] ?? '';
        $timeframe = $args['timeframe'] ?? 'current';
        $focus_areas = $args['focus_areas'] ?? [];

        $prompt = "Generate a comprehensive AI industry report for sector: {$sector}. ";
        $prompt .= "Timeframe: {$timeframe}. ";
        if ( ! empty( $focus_areas ) ) {
            $prompt .= "Focus areas: " . implode( ', ', $focus_areas ) . ". ";
        }
        $prompt .= "Include: Executive Summary, Market Overview, Key Players, Trends, ";
        $prompt .= "Opportunities, Challenges, Predictions. Be data-driven and analytical.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are an AI industry analyst. Provide well-researched, data-driven analysis.",
            'max_tokens' => 4000,
        ] );

        return [
            'success' => $response['success'] ?? false,
            'data'    => [
                'content' => $response['content'] ?? '',
                'sector'  => $sector,
            ],
        ];
    }
}

add_action( 'plugins_loaded', function() {
    if ( class_exists( 'MCP_Enhanced_System' ) ) {
        MCP_Site_WealthFromAI::get_instance();
    }
}, 20 );
