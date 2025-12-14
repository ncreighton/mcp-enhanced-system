<?php
/**
 * Plugin Name: MCP Site Extension - Pulse Gear Reviews
 * Description: Site-specific MCP tools for fitness tech reviews and comparisons.
 * Version: 1.0.0
 * Author: Nick Creighton
 * Requires Plugins: mcp-enhanced-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MCP_Site_PulseGearReviews {

    private static $instance = null;
    private $site_key = 'pulsegearreviews';
    private $amazon_tag = 'pulsegearreviews-20';

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
        // Fitness Tech Review
        $tools[] = [
            'name'        => 'pgr_product_review',
            'description' => 'Generate comprehensive fitness tech product review.',
            'category'    => 'Pulse Gear Reviews',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'product_name'   => [ 'type' => 'string', 'description' => 'Product to review' ],
                    'product_type'   => [ 'type' => 'string', 'description' => 'Category (smartwatch, tracker, headphones, etc.)' ],
                    'key_features'   => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
                    'target_user'    => [ 'type' => 'string', 'description' => 'Target user profile' ],
                    'price_point'    => [ 'type' => 'string', 'description' => 'Price range' ],
                ],
                'required'   => [ 'product_name', 'product_type' ],
            ],
        ];

        // Comparison Table Generator
        $tools[] = [
            'name'        => 'pgr_comparison_table',
            'description' => 'Generate spec comparison table for fitness products.',
            'category'    => 'Pulse Gear Reviews',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'products' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Products to compare' ],
                    'specs'    => [ 'type' => 'array', 'items' => [ 'type' => 'string' ], 'description' => 'Specs to include' ],
                ],
                'required'   => [ 'products' ],
            ],
        ];

        // Best Of Roundup
        $tools[] = [
            'name'        => 'pgr_best_roundup',
            'description' => 'Generate "Best Of" roundup article for fitness gear.',
            'category'    => 'Pulse Gear Reviews',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'category'       => [ 'type' => 'string', 'description' => 'Product category' ],
                    'year'           => [ 'type' => 'string', 'description' => 'Year for the roundup' ],
                    'num_products'   => [ 'type' => 'integer', 'default' => 10 ],
                    'use_case'       => [ 'type' => 'string', 'description' => 'Specific use case (running, gym, swimming, etc.)' ],
                ],
                'required'   => [ 'category' ],
            ],
        ];

        // Workout Integration Guide
        $tools[] = [
            'name'        => 'pgr_workout_guide',
            'description' => 'Generate guide on using tech for specific workouts.',
            'category'    => 'Pulse Gear Reviews',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'workout_type' => [ 'type' => 'string', 'description' => 'Type of workout' ],
                    'tech_focus'   => [ 'type' => 'string', 'description' => 'Tech category to focus on' ],
                    'fitness_level' => [ 'type' => 'string', 'enum' => [ 'beginner', 'intermediate', 'advanced' ] ],
                ],
                'required'   => [ 'workout_type' ],
            ],
        ];

        // Tech vs Traditional
        $tools[] = [
            'name'        => 'pgr_tech_vs_traditional',
            'description' => 'Compare tech solution vs traditional fitness methods.',
            'category'    => 'Pulse Gear Reviews',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'tech_solution'   => [ 'type' => 'string', 'description' => 'Tech solution' ],
                    'traditional'     => [ 'type' => 'string', 'description' => 'Traditional method' ],
                    'fitness_goal'    => [ 'type' => 'string', 'description' => 'Fitness goal context' ],
                ],
                'required'   => [ 'tech_solution', 'traditional' ],
            ],
        ];

        return $tools;
    }

    public function handle_callbacks( $result, $tool, $args ) {
        switch ( $tool ) {
            case 'pgr_product_review':
                return $this->generate_product_review( $args );
            case 'pgr_comparison_table':
                return $this->generate_comparison_table( $args );
            case 'pgr_best_roundup':
                return $this->generate_best_roundup( $args );
            case 'pgr_workout_guide':
                return $this->generate_workout_guide( $args );
            case 'pgr_tech_vs_traditional':
                return $this->generate_tech_vs_traditional( $args );
            default:
                return $result;
        }
    }

    private function generate_product_review( $args ) {
        $name = $args['product_name'] ?? '';
        $type = $args['product_type'] ?? '';
        $features = $args['key_features'] ?? [];
        $target = $args['target_user'] ?? '';
        $price = $args['price_point'] ?? '';

        $prompt = "Write a comprehensive review of {$name} ({$type}). ";
        if ( ! empty( $features ) ) $prompt .= "Key features: " . implode( ', ', $features ) . ". ";
        if ( $target ) $prompt .= "Target user: {$target}. ";
        if ( $price ) $prompt .= "Price: {$price}. ";
        $prompt .= "Include: Quick Verdict, Design & Build, Features & Performance, ";
        $prompt .= "Battery Life, App Experience, Pros & Cons, Who Should Buy, Rating. ";
        $prompt .= "Write from hands-on testing perspective. Be honest about limitations.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a fitness tech reviewer who actually tests products. Provide detailed, honest reviews with real-world testing insights. Use an athletic, energetic voice.",
            'max_tokens' => 3500,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '', 'affiliate_tag' => $this->amazon_tag ] ];
    }

    private function generate_comparison_table( $args ) {
        $products = $args['products'] ?? [];
        $specs = $args['specs'] ?? [ 'price', 'battery life', 'water resistance', 'GPS', 'heart rate', 'weight' ];

        $prompt = "Create a detailed spec comparison table for: " . implode( ', ', $products ) . ". ";
        $prompt .= "Specs to include: " . implode( ', ', $specs ) . ". ";
        $prompt .= "Format as clean markdown table with accurate specifications. Include a winner row.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a fitness tech analyst. Create accurate, well-researched comparison tables.",
            'max_tokens' => 2000,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_best_roundup( $args ) {
        $category = $args['category'] ?? '';
        $year = $args['year'] ?? date( 'Y' );
        $num = $args['num_products'] ?? 10;
        $use_case = $args['use_case'] ?? '';

        $prompt = "Create a 'Best {$category} of {$year}' roundup article. ";
        $prompt .= "Include top {$num} products. ";
        if ( $use_case ) $prompt .= "Focus on: {$use_case}. ";
        $prompt .= "For each product include: Quick review, Key specs, Pros/Cons, Best for, Price. ";
        $prompt .= "Include overall buying guide and FAQs.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a fitness gear expert. Create authoritative, well-researched roundup articles.",
            'max_tokens' => 5000,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '', 'affiliate_tag' => $this->amazon_tag ] ];
    }

    private function generate_workout_guide( $args ) {
        $workout = $args['workout_type'] ?? '';
        $tech = $args['tech_focus'] ?? '';
        $level = $args['fitness_level'] ?? 'intermediate';

        $prompt = "Create a guide: Best Tech for {$workout} ({$level} level). ";
        if ( $tech ) $prompt .= "Focus on: {$tech}. ";
        $prompt .= "Include: Why tech helps, Recommended gear, How to use it, ";
        $prompt .= "Metrics to track, Tips for beginners. Be practical and actionable.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a certified personal trainer and tech enthusiast. Provide practical, safe fitness advice.",
            'max_tokens' => 3000,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }

    private function generate_tech_vs_traditional( $args ) {
        $tech = $args['tech_solution'] ?? '';
        $traditional = $args['traditional'] ?? '';
        $goal = $args['fitness_goal'] ?? '';

        $prompt = "Compare {$tech} vs {$traditional} for fitness. ";
        if ( $goal ) $prompt .= "Goal: {$goal}. ";
        $prompt .= "Include: How each works, Pros/Cons, Effectiveness, Cost, ";
        $prompt .= "Best situations for each, Verdict. Be balanced and evidence-based.";

        $response = mcp_ai()->execute( 'content_generation', $prompt, [
            'system'     => "You are a fitness science writer. Provide balanced, evidence-based comparisons.",
            'max_tokens' => 3000,
        ] );

        return [ 'success' => $response['success'] ?? false, 'data' => [ 'content' => $response['content'] ?? '' ] ];
    }
}

add_action( 'plugins_loaded', function() {
    if ( class_exists( 'MCP_Enhanced_System' ) ) {
        MCP_Site_PulseGearReviews::get_instance();
    }
}, 20 );
