<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="tabs-1">
    <?php
    $wpaicg_ai_model = get_option('wpaicg_ai_model','');
    ?>
    <div class="wpcgai_form_row">
        <label class="wpcgai_label" for="wpaicg_ai_model">Model:</label>
        <select class="regular-text" id="wpaicg_ai_model"  name="wpaicg_ai_model" >
            <?php
            foreach($wpaicg_custom_models as $wpaicg_custom_model){
                echo '<option'.($wpaicg_ai_model == $wpaicg_custom_model ? ' selected':'').' value="'.esc_html($wpaicg_custom_model).'">'.esc_html($wpaicg_custom_model).'</option>';
                if($wpaicg_custom_model == 'text-davinci-003'){
                    echo '<option'.($wpaicg_ai_model == 'gpt-3.5-turbo' ? ' selected':'').' value="gpt-3.5-turbo">gpt-3.5-turbo</option>';
                }
            }
            ?>
        </select>
        <a class="wpcgai_help_link" href="https://gptaipower.com/gpt-3-model-settings/" target="_blank">?</a>
        <a class="wpaicg_sync_finetune" href="javascript:void(0)">Sync</a>
    </div>
    <?php
    $wpaicg_sleep_time = get_option('wpaicg_sleep_time',8);
    ?>
    <div class="wpcgai_form_row wpaicg_sleep_time" style="<?php echo $wpaicg_ai_model == 'gpt-3.5-turbo' ? '' : 'display:none'?>">
        <label class="wpcgai_label">Sleep Time?:</label>
        <select class="regular-text"  name="wpaicg_sleep_time" >
            <?php
            for($i=1;$i<=10;$i++){
                echo '<option'.($wpaicg_sleep_time == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
            }
            ?>
        </select>
        seconds
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Temperature:</label>
        <input type="text" class="regular-text" id="label_temperature" name="chatgpt_settings[temperature]" value="<?php
        echo  esc_html( $db_Value['temperature'] ) ;
        ?>">
        <a class="wpcgai_help_link" href="https://gptaipower.com/gpt-3-temperature-settings/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Max Tokens:</label>
        <input type="text" class="regular-text" id="label_max_tokens" name="chatgpt_settings[max_tokens]" value="<?php
        echo  esc_html( $db_Value['max_tokens'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/max-tokens/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Top P:</label>
        <input type="text" class="regular-text" id="label_top_p" name="chatgpt_settings[top_p]" value="<?php
        echo  esc_html( $db_Value['top_p'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/top_p/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Best Of:</label>
        <input type="text" class="regular-text" id="label_best_of" name="chatgpt_settings[best_of]" value="<?php
        echo  esc_html( $db_Value['best_of'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/best-of/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Frequency Penalty:</label>
        <input type="text" class="regular-text" id="label_frequency_penalty" name="chatgpt_settings[frequency_penalty]" value="<?php
        echo  esc_html( $db_Value['frequency_penalty'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/frequency-penalty/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Presence Penalty:</label>
        <input type="text" class="regular-text" id="label_presence_penalty" name="chatgpt_settings[presence_penalty]" value="<?php
        echo  esc_html( $db_Value['presence_penalty'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/presence-penalty/" target="_blank">?</a>
    </div>

    <div class="wpcgai_form_row">
        <label class="wpcgai_label">Api Key:</label>
        <input type="text" class="regular-text" id="label_api_key" name="chatgpt_settings[api_key]" value="<?php
        echo  esc_html( $db_Value['api_key'] ) ;
        ?>" >
        <a class="wpcgai_help_link" href="https://gptaipower.com/bring-your-own-key-model/" target="_blank">?</a>
        <a class="wpcgai_help_link" href="https://beta.openai.com/account/api-keys" target="_blank">Get Your Api Key</a>
    </div>
</div>
<script>
    jQuery(document).ready(function ($){
        $('#wpaicg_ai_model').on('change', function (){
            if($(this).val() === 'gpt-3.5-turbo'){
                $('.wpaicg_sleep_time').show();
            }
            else{
                $('.wpaicg_sleep_time').hide();
            }
        })
    })
</script>
