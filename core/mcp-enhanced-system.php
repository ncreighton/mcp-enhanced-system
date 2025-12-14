<?php
/**
 * Plugin Name: MCP Enhanced System
 * Description: Core system for enhanced MCP tools across all sites. Provides API integrations (OpenAI, Anthropic, OpenRouter, Replicate), memory system, and extensible architecture for site-specific plugins.
 * Version: 1.0.0
 * Author: Nick Creighton
 * Network: true
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'MCP_ENHANCED_VERSION', '1.0.0' );
define( 'MCP_ENHANCED_PATH', plugin_dir_path( __FILE__ ) );
define( 'MCP_ENHANCED_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main MCP Enhanced System Class
 */
class MCP_Enhanced_System {

    private static $instance = null;
    
    // API Clients
    private $openai_client = null;
    private $anthropic_client = null;
    private $openrouter_client = null;
    private $replicate_client = null;
    
    // Memory System
    private $memory_store = [];
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ], 5 );
        add_filter( 'mwai_mcp_tools', [ $this, 'register_core_tools' ], 5 );
        add_filter( 'mwai_mcp_callback', [ $this, 'handle_core_callbacks' ], 5, 3 );
    }

    public function init() {
        $this->init_api_clients();
        $this->init_memory_system();
        do_action( 'mcp_enhanced_loaded' );
    }

    /**
     * =========================================
     * API CLIENTS INITIALIZATION
     * =========================================
     */
    
    private function init_api_clients() {
        // Get API keys from options or constants
        $this->openai_key = defined( 'OPENAI_API_KEY' ) ? OPENAI_API_KEY : get_option( 'mcp_enhanced_openai_key', '' );
        $this->anthropic_key = defined( 'ANTHROPIC_API_KEY' ) ? ANTHROPIC_API_KEY : get_option( 'mcp_enhanced_anthropic_key', '' );
        $this->openrouter_key = defined( 'OPENROUTER_API_KEY' ) ? OPENROUTER_API_KEY : get_option( 'mcp_enhanced_openrouter_key', '' );
        $this->replicate_key = defined( 'REPLICATE_API_KEY' ) ? REPLICATE_API_KEY : get_option( 'mcp_enhanced_replicate_key', '' );
    }

    /**
     * =========================================
     * MEMORY SYSTEM
     * =========================================
     */
    
    private function init_memory_system() {
        // Load persistent memory from database
        $this->memory_store = get_option( 'mcp_enhanced_memory', [] );
    }

    public function memory_set( $key, $value, $context = 'global' ) {
        if ( ! isset( $this->memory_store[ $context ] ) ) {
            $this->memory_store[ $context ] = [];
        }
        $this->memory_store[ $context ][ $key ] = [
            'value'     => $value,
            'timestamp' => time(),
        ];
        update_option( 'mcp_enhanced_memory', $this->memory_store );
        return true;
    }

    public function memory_get( $key, $context = 'global' ) {
        if ( isset( $this->memory_store[ $context ][ $key ] ) ) {
            return $this->memory_store[ $context ][ $key ]['value'];
        }
        return null;
    }

    public function memory_search( $query, $context = 'global', $limit = 10 ) {
        $results = [];
        $search_context = $context === 'all' ? $this->memory_store : [ $context => $this->memory_store[ $context ] ?? [] ];
        
        foreach ( $search_context as $ctx => $memories ) {
            foreach ( $memories as $key => $data ) {
                if ( stripos( $key, $query ) !== false || stripos( json_encode( $data['value'] ), $query ) !== false ) {
                    $results[] = [
                        'context' => $ctx,
                        'key'     => $key,
                        'value'   => $data['value'],
                    ];
                }
            }
        }
        
        return array_slice( $results, 0, $limit );
    }

    public function memory_list( $context = 'global' ) {
        return $this->memory_store[ $context ] ?? [];
    }

    public function memory_delete( $key, $context = 'global' ) {
        if ( isset( $this->memory_store[ $context ][ $key ] ) ) {
            unset( $this->memory_store[ $context ][ $key ] );
            update_option( 'mcp_enhanced_memory', $this->memory_store );
            return true;
        }
        return false;
    }

    /**
     * =========================================
     * API EXECUTION METHODS
     * =========================================
     */

    public function execute_openai( $prompt, $options = [] ) {
        if ( empty( $this->openai_key ) ) {
            return [ 'success' => false, 'error' => 'OpenAI API key not configured' ];
        }

        $defaults = [
            'model'       => 'gpt-4o',
            'max_tokens'  => 2000,
            'temperature' => 0.7,
            'system'      => 'You are a helpful assistant.',
        ];
        $options = wp_parse_args( $options, $defaults );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openai_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'model'       => $options['model'],
                'messages'    => [
                    [ 'role' => 'system', 'content' => $options['system'] ],
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
                'max_tokens'  => $options['max_tokens'],
                'temperature' => $options['temperature'],
            ] ),
            'timeout' => 120,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['choices'][0]['message']['content'] ) ) {
            return [
                'success' => true,
                'content' => $body['choices'][0]['message']['content'],
                'model'   => $options['model'],
                'usage'   => $body['usage'] ?? [],
            ];
        }

        return [ 'success' => false, 'error' => $body['error']['message'] ?? 'Unknown error' ];
    }

    public function execute_anthropic( $prompt, $options = [] ) {
        if ( empty( $this->anthropic_key ) ) {
            return [ 'success' => false, 'error' => 'Anthropic API key not configured' ];
        }

        $defaults = [
            'model'       => 'claude-sonnet-4-20250514',
            'max_tokens'  => 2000,
            'temperature' => 0.7,
            'system'      => 'You are a helpful assistant.',
        ];
        $options = wp_parse_args( $options, $defaults );

        $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key'         => $this->anthropic_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'model'       => $options['model'],
                'system'      => $options['system'],
                'messages'    => [
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
                'max_tokens'  => $options['max_tokens'],
            ] ),
            'timeout' => 120,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['content'][0]['text'] ) ) {
            return [
                'success' => true,
                'content' => $body['content'][0]['text'],
                'model'   => $options['model'],
                'usage'   => $body['usage'] ?? [],
            ];
        }

        return [ 'success' => false, 'error' => $body['error']['message'] ?? 'Unknown error' ];
    }

    public function execute_openrouter( $prompt, $options = [] ) {
        if ( empty( $this->openrouter_key ) ) {
            return [ 'success' => false, 'error' => 'OpenRouter API key not configured' ];
        }

        $defaults = [
            'model'       => 'anthropic/claude-3.5-sonnet',
            'max_tokens'  => 2000,
            'temperature' => 0.7,
            'system'      => 'You are a helpful assistant.',
        ];
        $options = wp_parse_args( $options, $defaults );

        $response = wp_remote_post( 'https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openrouter_key,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => home_url(),
            ],
            'body'    => wp_json_encode( [
                'model'    => $options['model'],
                'messages' => [
                    [ 'role' => 'system', 'content' => $options['system'] ],
                    [ 'role' => 'user', 'content' => $prompt ],
                ],
            ] ),
            'timeout' => 120,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['choices'][0]['message']['content'] ) ) {
            return [
                'success' => true,
                'content' => $body['choices'][0]['message']['content'],
                'model'   => $options['model'],
            ];
        }

        return [ 'success' => false, 'error' => $body['error']['message'] ?? 'Unknown error' ];
    }

    public function execute_replicate( $model, $input, $options = [] ) {
        if ( empty( $this->replicate_key ) ) {
            return [ 'success' => false, 'error' => 'Replicate API key not configured' ];
        }

        // Create prediction
        $response = wp_remote_post( 'https://api.replicate.com/v1/predictions', [
            'headers' => [
                'Authorization' => 'Token ' . $this->replicate_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'model' => $model,
                'input' => $input,
            ] ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['id'] ) ) {
            // Poll for completion
            $prediction_url = $body['urls']['get'];
            $max_attempts = 60;
            $attempt = 0;
            
            while ( $attempt < $max_attempts ) {
                sleep( 2 );
                $status_response = wp_remote_get( $prediction_url, [
                    'headers' => [
                        'Authorization' => 'Token ' . $this->replicate_key,
                    ],
                ] );
                
                $status_body = json_decode( wp_remote_retrieve_body( $status_response ), true );
                
                if ( $status_body['status'] === 'succeeded' ) {
                    return [
                        'success' => true,
                        'output'  => $status_body['output'],
                        'model'   => $model,
                    ];
                } elseif ( $status_body['status'] === 'failed' ) {
                    return [ 'success' => false, 'error' => $status_body['error'] ?? 'Prediction failed' ];
                }
                
                $attempt++;
            }
            
            return [ 'success' => false, 'error' => 'Prediction timeout' ];
        }

        return [ 'success' => false, 'error' => $body['detail'] ?? 'Unknown error' ];
    }

    /**
     * =========================================
     * CORE MCP TOOLS REGISTRATION
     * =========================================
     */

    public function register_core_tools( $tools ) {
        // Memory Tools
        $tools[] = [
            'name'        => 'mcp_memory_set',
            'description' => 'Store a value in persistent memory.',
            'category'    => 'Memory System',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'key'     => [ 'type' => 'string', 'description' => 'Memory key' ],
                    'value'   => [ 'type' => 'string', 'description' => 'Value to store (can be JSON)' ],
                    'context' => [ 'type' => 'string', 'description' => 'Memory context/namespace', 'default' => 'global' ],
                ],
                'required'   => [ 'key', 'value' ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_memory_get',
            'description' => 'Retrieve a value from persistent memory.',
            'category'    => 'Memory System',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'key'     => [ 'type' => 'string', 'description' => 'Memory key to retrieve' ],
                    'context' => [ 'type' => 'string', 'description' => 'Memory context/namespace', 'default' => 'global' ],
                ],
                'required'   => [ 'key' ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_memory_search',
            'description' => 'Search through stored memories.',
            'category'    => 'Memory System',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'query'   => [ 'type' => 'string', 'description' => 'Search query' ],
                    'context' => [ 'type' => 'string', 'description' => 'Context to search (or "all")', 'default' => 'global' ],
                    'limit'   => [ 'type' => 'integer', 'description' => 'Max results', 'default' => 10 ],
                ],
                'required'   => [ 'query' ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_memory_list',
            'description' => 'List all memories in a context.',
            'category'    => 'Memory System',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'context' => [ 'type' => 'string', 'description' => 'Memory context/namespace', 'default' => 'global' ],
                ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_memory_delete',
            'description' => 'Delete a memory entry.',
            'category'    => 'Memory System',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'key'     => [ 'type' => 'string', 'description' => 'Memory key to delete' ],
                    'context' => [ 'type' => 'string', 'description' => 'Memory context/namespace', 'default' => 'global' ],
                ],
                'required'   => [ 'key' ],
            ],
        ];

        // Multi-API Tools
        $tools[] = [
            'name'        => 'mcp_ai_generate',
            'description' => 'Generate content using specified AI provider (openai, anthropic, openrouter).',
            'category'    => 'AI Generation',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'prompt'      => [ 'type' => 'string', 'description' => 'The prompt to send' ],
                    'provider'    => [ 'type' => 'string', 'enum' => [ 'openai', 'anthropic', 'openrouter' ], 'default' => 'openai' ],
                    'model'       => [ 'type' => 'string', 'description' => 'Model to use (provider-specific)' ],
                    'system'      => [ 'type' => 'string', 'description' => 'System prompt' ],
                    'max_tokens'  => [ 'type' => 'integer', 'default' => 2000 ],
                    'temperature' => [ 'type' => 'number', 'default' => 0.7 ],
                ],
                'required'   => [ 'prompt' ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_image_generate',
            'description' => 'Generate images using Replicate models.',
            'category'    => 'AI Generation',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'prompt'        => [ 'type' => 'string', 'description' => 'Image generation prompt' ],
                    'model'         => [ 'type' => 'string', 'description' => 'Replicate model ID', 'default' => 'stability-ai/sdxl' ],
                    'width'         => [ 'type' => 'integer', 'default' => 1024 ],
                    'height'        => [ 'type' => 'integer', 'default' => 1024 ],
                    'negative_prompt' => [ 'type' => 'string', 'description' => 'What to avoid' ],
                ],
                'required'   => [ 'prompt' ],
            ],
        ];

        // Project/Session Tools
        $tools[] = [
            'name'        => 'mcp_load_project_context',
            'description' => 'Load saved project context including skills, rules, and memory.',
            'category'    => 'Project Management',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'project_id' => [ 'type' => 'string', 'description' => 'Project identifier (e.g., site slug)' ],
                ],
                'required'   => [ 'project_id' ],
            ],
        ];

        $tools[] = [
            'name'        => 'mcp_save_project_context',
            'description' => 'Save current project context for future sessions.',
            'category'    => 'Project Management',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'project_id' => [ 'type' => 'string', 'description' => 'Project identifier' ],
                    'context'    => [ 'type' => 'string', 'description' => 'JSON context data to save' ],
                ],
                'required'   => [ 'project_id', 'context' ],
            ],
        ];

        return $tools;
    }

    /**
     * =========================================
     * CORE CALLBACKS HANDLER
     * =========================================
     */

    public function handle_core_callbacks( $result, $tool, $args ) {
        // Memory Tools
        if ( 'mcp_memory_set' === $tool ) {
            $value = $args['value'];
            // Try to decode JSON
            $decoded = json_decode( $value, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                $value = $decoded;
            }
            $success = $this->memory_set( $args['key'], $value, $args['context'] ?? 'global' );
            return [ 'success' => $success, 'message' => $success ? 'Memory stored' : 'Failed to store memory' ];
        }

        if ( 'mcp_memory_get' === $tool ) {
            $value = $this->memory_get( $args['key'], $args['context'] ?? 'global' );
            return [ 'success' => $value !== null, 'data' => $value ];
        }

        if ( 'mcp_memory_search' === $tool ) {
            $results = $this->memory_search( $args['query'], $args['context'] ?? 'global', $args['limit'] ?? 10 );
            return [ 'success' => true, 'data' => $results ];
        }

        if ( 'mcp_memory_list' === $tool ) {
            $memories = $this->memory_list( $args['context'] ?? 'global' );
            return [ 'success' => true, 'data' => $memories ];
        }

        if ( 'mcp_memory_delete' === $tool ) {
            $success = $this->memory_delete( $args['key'], $args['context'] ?? 'global' );
            return [ 'success' => $success, 'message' => $success ? 'Memory deleted' : 'Memory not found' ];
        }

        // AI Generation
        if ( 'mcp_ai_generate' === $tool ) {
            $provider = $args['provider'] ?? 'openai';
            $options = [
                'model'       => $args['model'] ?? null,
                'system'      => $args['system'] ?? 'You are a helpful assistant.',
                'max_tokens'  => $args['max_tokens'] ?? 2000,
                'temperature' => $args['temperature'] ?? 0.7,
            ];
            
            switch ( $provider ) {
                case 'anthropic':
                    $options['model'] = $options['model'] ?? 'claude-sonnet-4-20250514';
                    return $this->execute_anthropic( $args['prompt'], $options );
                case 'openrouter':
                    $options['model'] = $options['model'] ?? 'anthropic/claude-3.5-sonnet';
                    return $this->execute_openrouter( $args['prompt'], $options );
                default:
                    $options['model'] = $options['model'] ?? 'gpt-4o';
                    return $this->execute_openai( $args['prompt'], $options );
            }
        }

        // Image Generation
        if ( 'mcp_image_generate' === $tool ) {
            $model = $args['model'] ?? 'stability-ai/sdxl';
            $input = [
                'prompt'          => $args['prompt'],
                'width'           => $args['width'] ?? 1024,
                'height'          => $args['height'] ?? 1024,
                'negative_prompt' => $args['negative_prompt'] ?? '',
            ];
            return $this->execute_replicate( $model, $input );
        }

        // Project Context
        if ( 'mcp_load_project_context' === $tool ) {
            $project_id = $args['project_id'];
            $context = get_option( "mcp_project_context_{$project_id}", null );
            if ( $context ) {
                return [
                    'success' => true,
                    'data'    => $context,
                    'message' => "Loaded project context for {$project_id}",
                ];
            }
            return [ 'success' => false, 'message' => "No saved context for {$project_id}" ];
        }

        if ( 'mcp_save_project_context' === $tool ) {
            $project_id = $args['project_id'];
            $context = json_decode( $args['context'], true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                $context = $args['context']; // Store as string if not valid JSON
            }
            update_option( "mcp_project_context_{$project_id}", $context );
            return [ 'success' => true, 'message' => "Saved project context for {$project_id}" ];
        }

        return $result;
    }
}

// Global accessor function
function mcp_enhanced() {
    return MCP_Enhanced_System::get_instance();
}

// Initialize
MCP_Enhanced_System::get_instance();
