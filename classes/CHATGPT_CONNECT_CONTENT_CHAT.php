<?php

namespace CHATGPT_CONNECT;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\CHATGPT_CONNECT\\CHATGPT_CONNECT_CONTENT_CHAT')) {
    class CHATGPT_CONNECT_CONTENT_CHAT
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'wpaicg_menu' ) );
            add_shortcode( 'wpaicg_chatgpt', [ $this, 'wpaicg_chatbox' ] );
            add_shortcode( 'wpaicg_chatgpt_widget', [ $this, 'wpaicg_chatbox_widget' ] );
            add_action( 'wp_ajax_wpaicg_chatbox_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_chatbox_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_wpaicg_chat_shortcode_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_chat_shortcode_message', array( $this, 'wpaicg_chatbox_message' ) );
        }

        public function wpaicg_menu()
        {
            add_submenu_page(
                'wpaicg',
                'ChatGPT',
                'ChatGPT',
                'manage_options',
                'wpaicg_chatgpt',
                array( $this, 'wpaicg_chatmode' )
            );
        }

        public function wpaicg_chatmode()
        {
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatmode.php';
        }

        public function wpaicg_chatbox_message()
        {
            global  $wpdb ;
            $wpaicg_client_id = $_COOKIE['PHPSESSID'];
            $wpaicg_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            $open_ai = WPAICG_OpenAI::get_instance()->openai();
            if (!$open_ai) {
                $wpaicg_result['msg'] = 'Missing API Setting';
                wp_send_json($wpaicg_result);
                exit;
            }
            $wpaicg_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if ( !wp_verify_nonce( $wpaicg_nonce, 'wpaicg-chatbox' ) ) {
                $wpaicg_result['msg'] = 'Security check';
            } else {
                $wpaicg_message = ( isset( $_REQUEST['message'] ) && !empty($_REQUEST['message']) ? sanitize_text_field( $_REQUEST['message'] ) : '' );
                $url = ( isset( $_REQUEST['url'] ) && !empty($_REQUEST['url']) ? sanitize_text_field( $_REQUEST['url'] ) : '' );
                $wpaicg_pinecone_api = get_option('wpaicg_pinecone_api', '');
                $wpaicg_pinecone_environment = get_option('wpaicg_pinecone_environment', '');
                $wpaicg_total_tokens = 0;
                if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpaicg_chat_shortcode_message'){
                    $table = $wpdb->prefix . 'wpaicg';
                    $db_Value = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'wpaicg_settings' ), ARRAY_A );
                    $wpaicg_chat_shortcode_options = get_option('wpaicg_chat_shortcode_options',[]);
                    $default_setting = array(
                        'language' => 'en',
                        'tone' => 'friendly',
                        'profession' => 'none',
                        'model' => 'text-davinci-003',
                        'temperature' => $db_Value['temperature'],
                        'max_tokens' => $db_Value['max_tokens'],
                        'top_p' => $db_Value['top_p'],
                        'best_of' => $db_Value['best_of'],
                        'frequency_penalty' => $db_Value['frequency_penalty'],
                        'presence_penalty' => $db_Value['presence_penalty'],
                        'ai_name' => 'AI',
                        'you' => 'You',
                        'ai_thinking' => 'AI Thinking',
                        'placeholder' => 'Type a message',
                        'welcome' => 'Hello human, I am a GPT3 powered AI chat bot. Ask me anything!',
                        'remember_conversation' => 'yes',
                        'conversation_cut' => 10,
                        'content_aware' => 'yes',
                        'embedding' =>  false,
                        'embedding_type' =>  false,
                        'embedding_top' =>  false,
                        'no_answer' => '',
                        'fontsize' => 13,
                        'fontcolor' => '#fff',
                        'user_bg_color' => '#444654',
                        'ai_bg_color' => '#343541',
                        'ai_icon_url' => '',
                        'ai_icon' => 'default',
                        'use_avatar' => false,
                        'save_logs' => false,
                        'chat_addition' => false,
                        'chat_addition_text' => '',
                    );
                    $wpaicg_settings = shortcode_atts($default_setting, $wpaicg_chat_shortcode_options);

                    if(isset($_REQUEST['wpaicg_chat_shortcode_options']) && is_array($_REQUEST['wpaicg_chat_shortcode_options'])){
                        $wpaicg_chat_shortcode_options = wpaicg_util_core()->sanitize_text_or_array_field($_REQUEST['wpaicg_chat_shortcode_options']);
                        $wpaicg_settings = shortcode_atts($wpaicg_settings, $wpaicg_chat_shortcode_options);
                    }

                    $wpaicg_chat_embedding = isset($wpaicg_settings['embedding']) && $wpaicg_settings['embedding'] ? true : false;
                    $wpaicg_chat_embedding_type = isset($wpaicg_settings['embedding_type']) ? $wpaicg_settings['embedding_type'] : '' ;
                    $wpaicg_chat_no_answer = isset($wpaicg_settings['no_answer']) ? $wpaicg_settings['no_answer'] : '' ;
                    $wpaicg_chat_embedding_top = isset($wpaicg_settings['embedding_top']) ? $wpaicg_settings['embedding_top'] : 1 ;
                    $wpaicg_chat_no_answer = empty($wpaicg_chat_no_answer) ? 'I dont know' : $wpaicg_chat_no_answer;
                    $wpaicg_chat_with_embedding = false;
                    $wpaicg_chat_language = isset($wpaicg_settings['language']) ? $wpaicg_settings['language'] : 'en' ;
                    $wpaicg_chat_tone = isset($wpaicg_settings['tone']) ? $wpaicg_settings['tone'] : 'friendly' ;
                    $wpaicg_chat_proffesion = isset($wpaicg_settings['profession']) ? $wpaicg_settings['profession'] : 'none' ;
                    $wpaicg_chat_remember_conversation = isset($wpaicg_settings['remember_conversation']) ? $wpaicg_settings['remember_conversation'] : 'yes' ;
                    $wpaicg_chat_content_aware = isset($wpaicg_settings['content_aware']) ? $wpaicg_settings['content_aware'] : 'yes' ;
                    $wpaicg_ai_model = isset($wpaicg_settings['model']) ? $wpaicg_settings['model'] : 'gpt-3.5-turbo' ;
                    $wpaicg_conversation_cut = isset($wpaicg_settings['conversation_cut']) ? $wpaicg_settings['conversation_cut'] : 10 ;
                    $wpaicg_conversation_url = 'wpaicg_conversation_url_shortcode';
                    $wpaicg_save_logs = isset($wpaicg_settings['save_logs']) && $wpaicg_settings['save_logs'] ? true : false;
                    $wpaicg_chat_addition = isset($wpaicg_settings['chat_addition']) && $wpaicg_settings['chat_addition'] ? true : false;
                    $wpaicg_chat_addition_text = isset($wpaicg_settings['chat_addition_text']) && !empty($wpaicg_settings['chat_addition_text']) ? $wpaicg_settings['chat_addition_text'] : '';
                }
                else {
                    $wpaicg_chat_widget = get_option('wpaicg_chat_widget', []);
                    $wpaicg_chat_embedding = get_option('wpaicg_chat_embedding', false);
                    $wpaicg_chat_embedding_type = get_option('wpaicg_chat_embedding_type', false);
                    $wpaicg_chat_no_answer = get_option('wpaicg_chat_no_answer', '');
                    $wpaicg_chat_embedding_top = get_option('wpaicg_chat_embedding_top', 1);
                    $wpaicg_chat_no_answer = empty($wpaicg_chat_no_answer) ? 'I dont know' : $wpaicg_chat_no_answer;
                    $wpaicg_chat_with_embedding = false;
                    $wpaicg_chat_language = get_option('wpaicg_chat_language', 'en');
                    $wpaicg_chat_tone = isset($wpaicg_chat_widget['tone']) && !empty($wpaicg_chat_widget['tone']) ? $wpaicg_chat_widget['tone'] : 'friendly';
                    $wpaicg_chat_proffesion = isset($wpaicg_chat_widget['proffesion']) && !empty($wpaicg_chat_widget['proffesion']) ? $wpaicg_chat_widget['proffesion'] : 'none';
                    $wpaicg_chat_remember_conversation = isset($wpaicg_chat_widget['remember_conversation']) && !empty($wpaicg_chat_widget['remember_conversation']) ? $wpaicg_chat_widget['remember_conversation'] : 'yes';
                    $wpaicg_chat_content_aware = isset($wpaicg_chat_widget['content_aware']) && !empty($wpaicg_chat_widget['content_aware']) ? $wpaicg_chat_widget['content_aware'] : 'yes';
                    $wpaicg_ai_model = get_option('wpaicg_chat_model', 'text-davinci-003');
                    $wpaicg_conversation_cut = get_option('wpaicg_conversation_cut', 10);
                    $wpaicg_conversation_url = 'wpaicg_conversation_url';
                    $wpaicg_save_logs = isset($wpaicg_chat_widget['save_logs']) && $wpaicg_chat_widget['save_logs'] ? true : false;
                    $wpaicg_chat_addition = get_option('wpaicg_chat_addition',false);
                    $wpaicg_chat_addition_text = get_option('wpaicg_chat_addition_text','');
                }
                /*Start check Log*/
                $wpaicg_chat_log_id = false;
                $wpaicg_chat_log_data = array();
                if(!empty($wpaicg_message) && $wpaicg_save_logs) {
                    $wpaicg_chat_source = 'widget';
                    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpaicg_chat_shortcode_message') {
                        $wpaicg_chat_source = 'shortcode';
                    }
                    $wpaicg_current_context_id = isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : '';
                    $wpaicg_current_context_title = !empty($wpaicg_current_context_id) ? get_the_title($wpaicg_current_context_id) : '';
                    $wpaicg_unique_chat = md5($wpaicg_client_id . '-' . $wpaicg_current_context_id);
                    $wpaicg_chat_log_check = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "wpaicg_chatlogs WHERE source='" . $wpaicg_chat_source . "' AND log_session='" . $wpaicg_unique_chat . "'");
                    if (!$wpaicg_chat_log_check) {
                        $wpdb->insert($wpdb->prefix . 'wpaicg_chatlogs', array(
                            'log_session' => $wpaicg_unique_chat,
                            'data' => json_encode(array()),
                            'page_title' => $wpaicg_current_context_title,
                            'source' => $wpaicg_chat_source,
                            'created_at' => time()
                        ));
                        $wpaicg_chat_log_id = $wpdb->insert_id;
                    } else {
                        $wpaicg_chat_log_id = $wpaicg_chat_log_check->id;
                        $wpaicg_current_log_data = json_decode($wpaicg_chat_log_check->data, true);
                        if ($wpaicg_current_log_data && is_array($wpaicg_current_log_data)) {
                            $wpaicg_chat_log_data = $wpaicg_current_log_data;
                        }
                    }
                    $wpaicg_chat_log_data[] = array('message' => $wpaicg_message, 'type' => 'user', 'date' => time(),'ip' => $this->getIpAddress());
                }
                /*End Check Log*/
                $wpaicg_embedding_content = '';
                if($wpaicg_chat_embedding){
                    /*Using embeddings only*/
                    $wpaicg_embeddings_result = $this->wpaicg_embeddings_result($open_ai,$wpaicg_pinecone_api, $wpaicg_pinecone_environment, $wpaicg_message, $wpaicg_chat_embedding_top);
                    if(!$wpaicg_chat_embedding_type || empty($wpaicg_chat_embedding_type)){
                        $wpaicg_result['status'] = $wpaicg_embeddings_result['status'];
                        $wpaicg_result['data'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                        $wpaicg_result['msg'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                        $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai',$wpaicg_result['data']);
                        wp_send_json($wpaicg_result);
                        exit;
                    }
                    else{
                        $wpaicg_result['status'] = $wpaicg_embeddings_result['status'];
                        if($wpaicg_result['status'] == 'error'){
                            $wpaicg_result['msg'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                            $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai',$wpaicg_result['data']);
                            wp_send_json($wpaicg_result);
                            exit;
                        }
                        else{
                            $wpaicg_total_tokens += $wpaicg_embeddings_result['tokens']; // Add embedding tokens
                            $wpaicg_embedding_content = $wpaicg_embeddings_result['data'];
                        }
                        $wpaicg_chat_with_embedding = true;
                    }
                }
                if ($wpaicg_chat_remember_conversation == 'yes') {
                    $wpaicg_session_page = md5($wpaicg_client_id.$url);

                    if(!isset($_COOKIE[$wpaicg_conversation_url]) || empty($_COOKIE[$wpaicg_conversation_url])){
                        setcookie($wpaicg_conversation_url,$wpaicg_session_page,time()+86400,COOKIEPATH, COOKIE_DOMAIN);
                        $wpaicg_conversation_messages = array();
                    }
                    else{
                        $wpaicg_conversation_messages = isset($_COOKIE[$wpaicg_session_page]) ? $_COOKIE[$wpaicg_session_page] : '';
                        $wpaicg_conversation_messages = str_replace("\\",'',$wpaicg_conversation_messages);
                        if(!empty($wpaicg_conversation_messages && is_serialized($wpaicg_conversation_messages))){
                            $wpaicg_conversation_messages = unserialize($wpaicg_conversation_messages);
                            $wpaicg_conversation_messages = $wpaicg_conversation_messages ? $wpaicg_conversation_messages : array();
                        }
                        else{
                            $wpaicg_conversation_messages = array();
                        }
                    }
                    $wpaicg_conversation_messages_length = count($wpaicg_conversation_messages);
                    if ($wpaicg_conversation_messages_length > $wpaicg_conversation_cut) {
                        $wpaicg_conversation_messages_start = $wpaicg_conversation_messages_length - $wpaicg_conversation_cut;
                    } else {
                        $wpaicg_conversation_messages_start = 0;
                    }
                    $wpaicg_conversation_end_messages = array_splice($wpaicg_conversation_messages, $wpaicg_conversation_messages_start, $wpaicg_conversation_messages_length);
                }

                if (!empty($wpaicg_message)) {
                    $wpaicg_language_file = WPAICG_PLUGIN_DIR . 'admin/chat/languages/' . $wpaicg_chat_language . '.json';
                    if (!file_exists($wpaicg_language_file)) {
                        $wpaicg_language_file = WPAICG_PLUGIN_DIR . 'admin/chat/languages/en.json';
                    }
                    $wpaicg_language_json = file_get_contents($wpaicg_language_file);
                    $wpaicg_languages = json_decode($wpaicg_language_json, true);
                    $wpaicg_chat_tone = isset($wpaicg_languages['tone'][$wpaicg_chat_tone]) ? $wpaicg_languages['tone'][$wpaicg_chat_tone] : 'Professional';
                    $wpaicg_chat_proffesion = isset($wpaicg_languages['proffesion'][$wpaicg_chat_proffesion]) ? $wpaicg_languages['proffesion'][$wpaicg_chat_proffesion] : 'none';


                    $wpaicg_greeting_key = 'greeting';

                    if ($wpaicg_chat_proffesion != 'none') {
                        $wpaicg_greeting_key .= '_proffesion';
                    }
                    $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n");
                    if ($wpaicg_chat_content_aware == 'yes') {
                        if($wpaicg_chat_with_embedding && !empty($wpaicg_embedding_content)){
                            $wpaicg_greeting_key .= '_content';
                            $current_context = '"'.$wpaicg_embedding_content.'"';
                            if ($wpaicg_chat_proffesion != 'none') {
                                $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n", $current_context);
                            } else {
                                $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone . ".\n", $current_context);
                            }
                        }
                        elseif(isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id'])){
                            $current_post = get_post(sanitize_text_field($_REQUEST['post_id']));
                            if ($current_post) {
                                $wpaicg_greeting_key .= '_content';
                                $current_context = '"' . strip_tags($current_post->post_title);
                                $current_post_excerpt = str_replace('[...]', '', strip_tags(get_the_excerpt($current_post)));
                                if ($current_post_excerpt !== '') {
                                    $current_post_excerpt = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                                        return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                                    }, $current_post_excerpt);
                                    $current_context .= "\n" . $current_post_excerpt;
                                }
                                $current_context .= '"';
                                if ($wpaicg_chat_proffesion != 'none') {
                                    $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n", $current_context);
                                } else {
                                    $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone . ".\n", $current_context);
                                }
                                if($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                                    $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                                }
                            }
                        }
                        elseif($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                            $wpaicg_greeting_key .= '_content';
                            $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                        }
                    }

                    $wpaicg_chatgpt_messages = array();
                    $wpaicg_chatgpt_messages[] = array('role' => 'system', 'content' => html_entity_decode($wpaicg_chat_greeting_message,ENT_QUOTES ,'UTF-8'));
                    if ($wpaicg_chat_remember_conversation == 'yes') {
                        $wpaicg_conversation_end_messages[] = 'Human: ' . $wpaicg_message . "\nAI: ";
                        foreach ($wpaicg_conversation_end_messages as $wpaicg_conversation_end_message) {
                            $wpaicg_chatgpt_message = $wpaicg_conversation_end_message;
                            if(strpos($wpaicg_conversation_end_message, 'Human: ') !== false){
                                $wpaicg_chatgpt_message = str_replace('Human: ','',$wpaicg_chatgpt_message);
                                $wpaicg_chatgpt_message = str_replace("\nAI: ",'',$wpaicg_chatgpt_message);
                                if(!empty($wpaicg_chatgpt_message)) {
                                    $wpaicg_chatgpt_messages[] = array('role' => 'user', 'content' => $wpaicg_chatgpt_message);
                                }
                            }
                            else{
                                if(!empty($wpaicg_chatgpt_message)) {
                                    $wpaicg_chatgpt_messages[] = array('role' => 'assistant', 'content' => $wpaicg_chatgpt_message);
                                }
                            }
                            $wpaicg_chat_greeting_message .= "\n" . $wpaicg_conversation_end_message;
                        }
                        $prompt = $wpaicg_chat_greeting_message;
                    } else {
                        $prompt = $wpaicg_chat_greeting_message . "\nHuman: " . $wpaicg_message . "\nAI: ";
                        $wpaicg_chatgpt_messages[] = array('role' => 'user','content' => $wpaicg_message);
                    }
                    $wpaicg_chat_temperature = get_option('wpaicg_chat_temperature',$open_ai->temperature);
                    $wpaicg_chat_max_tokens = get_option('wpaicg_chat_max_tokens',$open_ai->max_tokens);
                    $wpaicg_chat_top_p = get_option('wpaicg_chat_top_p',$open_ai->top_p);
                    $wpaicg_chat_best_of = get_option('wpaicg_chat_best_of',$open_ai->best_of);
                    $wpaicg_chat_frequency_penalty = get_option('wpaicg_chat_frequency_penalty',$open_ai->frequency_penalty);
                    $wpaicg_chat_presence_penalty = get_option('wpaicg_chat_presence_penalty',$open_ai->presence_penalty);
                    if($wpaicg_ai_model === 'gpt-3.5-turbo'){
                        $wpaicg_data_request = [
                            'model' => $wpaicg_ai_model,
                            'messages' => $wpaicg_chatgpt_messages,
                            'temperature' => floatval($wpaicg_chat_temperature),
                            'max_tokens' => intval($wpaicg_chat_max_tokens),
                            'frequency_penalty' => floatval($wpaicg_chat_frequency_penalty),
                            'presence_penalty' => floatval($wpaicg_chat_presence_penalty),
                            'top_p' => floatval($wpaicg_chat_top_p)
                        ];
                        $complete = $open_ai->chat($wpaicg_data_request);
                    }
                    else {
                        /*Old completion*/
                        $wpaicg_data_request = [
                            'model' => $wpaicg_ai_model,
                            'prompt' => $prompt,
                            'temperature' => floatval($wpaicg_chat_temperature),
                            'max_tokens' => intval($wpaicg_chat_max_tokens),
                            'frequency_penalty' => floatval($wpaicg_chat_frequency_penalty),
                            'presence_penalty' => floatval($wpaicg_chat_presence_penalty),
                            'top_p' => floatval($wpaicg_chat_top_p),
                            'best_of' => intval($wpaicg_chat_best_of)
                        ];
                        $complete = $open_ai->completion($wpaicg_data_request);
                        /*Chat implement*/
                    }
                    $complete = json_decode($complete);
                    if (isset($complete->error)) {
                        $wpaicg_result['status'] = 'error';
                        $wpaicg_result['data_request'] = $wpaicg_data_request;
                        $wpaicg_result['msg'] = esc_html(trim($complete->error->message));
                        $wpaicg_result['messages'] = $wpaicg_chatgpt_messages;
                        $wpaicg_result['prompt'] = $prompt;
                        $wpaicg_result['chat_embedding'] = $wpaicg_chat_embedding;
                        $wpaicg_result['chat_embedding_type'] = $wpaicg_chat_embedding_type;
                    } else {
                        if($wpaicg_ai_model === 'gpt-3.5-turbo'){
                            $wpaicg_result['data'] = $complete->choices[0]->message->content;
                        }
                        else {
                            $wpaicg_result['data'] = $complete->choices[0]->text;
                        }
                        $wpaicg_total_tokens += $complete->usage->total_tokens;
                        $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai',$wpaicg_result['data'],$wpaicg_total_tokens);
                        $wpaicg_result['prompt'] = $prompt;
                        $wpaicg_result['status'] = 'success';
                        $wpaicg_result['data_request'] = $wpaicg_data_request;
                        $wpaicg_result['chat_embedding'] = $wpaicg_chat_embedding;
                        $wpaicg_result['messages'] = $wpaicg_chatgpt_messages;
                        $wpaicg_result['chat_embedding_type'] = $wpaicg_chat_embedding_type;
                        if ($wpaicg_chat_remember_conversation == 'yes') {
                            $wpaicg_conversation_end_messages[] = $wpaicg_result['data'];
                            setcookie($wpaicg_session_page,serialize($wpaicg_conversation_end_messages),time()+86400,COOKIEPATH, COOKIE_DOMAIN);
                        }
                    }
                }

            }

            wp_send_json( $wpaicg_result );
        }
        public function getIpAddress()
        {
            $ipAddress = '';
            if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
                // to get shared ISP IP address
                $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // check for IPs passing through proxy servers
                // check if multiple IP addresses are set and take the first one
                $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($ipAddressList as $ip) {
                    if (! empty($ip)) {
                        // if you prefer, you can check for valid IP address here
                        $ipAddress = $ip;
                        break;
                    }
                }
            } else if (! empty($_SERVER['HTTP_X_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            } else if (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if (! empty($_SERVER['HTTP_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED'];
            } else if (! empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }
            return $ipAddress;
        }

        public function wpaicg_save_chat_log($wpaicg_log_id, $wpaicg_log_data,$type = 'user', $message = '',$tokens = 0)
        {
            global $wpdb;
            if($wpaicg_log_id){
                $wpaicg_log_data[] = array('message' => $message, 'type' => $type, 'date' => time(), 'token' => $tokens);
                $wpdb->update($wpdb->prefix.'wpaicg_chatlogs', array(
                    'data' => json_encode($wpaicg_log_data),
                    'created_at' => time()
                ), array(
                    'id' => $wpaicg_log_id
                ));
            }
        }

        public function wpaicg_embeddings_result($open_ai,$wpaicg_pinecone_api,$wpaicg_pinecone_environment,$wpaicg_message, $wpaicg_chat_embedding_top)
        {
            $result = array('status' => 'error','data' => '');
            if(!empty($wpaicg_pinecone_api) && !empty($wpaicg_pinecone_environment) ) {
                $response = $open_ai->embeddings([
                    'input' => $wpaicg_message,
                    'model' => 'text-embedding-ada-002'
                ]);
                $response = json_decode($response, true);
                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                } else {
                    $embedding = $response['data'][0]['embedding'];
                    if (!empty($embedding)) {
                        $result['tokens'] = $response['usage']['total_tokens'];
                        $headers = array(
                            'Content-Type' => 'application/json',
                            'Api-Key' => $wpaicg_pinecone_api
                        );
                        $response = wp_remote_post('https://' . $wpaicg_pinecone_environment . '/query', array(
                            'headers' => $headers,
                            'body' => json_encode(array(
                                'vector' => $embedding,
                                'topK' => $wpaicg_chat_embedding_top
                            ))
                        ));
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $body = json_decode($response['body'], true);
                            if ($body) {
                                if (isset($body['matches']) && is_array($body['matches']) && count($body['matches'])) {
                                    $data = '';
                                    foreach($body['matches'] as $match){
                                        $wpaicg_embedding = get_post($match['id']);
                                        if ($wpaicg_embedding) {
                                            $data .= empty($data) ? $wpaicg_embedding->post_content : "\n".$wpaicg_embedding->post_content;
                                        }

                                    }
                                    $result['data'] = $data;
                                    $result['status'] = 'success';
                                }
                            }
                            else{
                                $result['data'] = 'Pinecone return empty';
                            }
                        }
                    }
                }
            }
            else{
                $wpaicg_result['data'] = 'Missing PineCone Settings';
            }
            return $result;
        }

        public function wpaicg_chatbox()
        {
            ob_start();
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatbox.php';
            $wpaicg_chatbox = ob_get_clean();
            return $wpaicg_chatbox;
        }

        public function wpaicg_chatbox_widget()
        {
            ob_start();
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatbox_widget.php';
            $wpaicg_chatbox = ob_get_clean();
            return $wpaicg_chatbox;
        }
    }
    WPAICG_Chat::get_instance();
}
