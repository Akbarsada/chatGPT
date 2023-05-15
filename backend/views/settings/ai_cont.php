<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="ai_tab" class="col s12">
	<?php
	$chatgpt_ai_model = get_option('chatgpt_ai_model','');
	?>
    <div class="col s6">
        <div class="row">
            <div class="col s6">Model</div>
            <div class="input-field col s6">
                <select>
	                <?php
	                foreach($chatgpt_custom_models as $chatgpt_custom_model){
		                echo '<option'.($chatgpt_ai_model == $chatgpt_custom_model ? ' selected':'').' value="'.esc_html($chatgpt_custom_model).'">'.esc_html($chatgpt_custom_model).'</option>';
		                if($chatgpt_custom_model == 'text-davinci-003'){
			                echo '<option'.($chatgpt_ai_model == 'gpt-3.5-turbo' ? ' selected':'').' value="gpt-3.5-turbo">gpt-3.5-turbo</option>';

		                }
	                }
	                ?>



                    <option value="" disabled selected>Choose your option</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>

                <label>Materialize Select</label>
            </div>

        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Temperature</div>
            <div class="input-field col s6">

                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Max Tokens</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Top P</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Best Of</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Frequency Penalty</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Beschreibung</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
        <!--Field 1-->
        <div class="row">
            <div class="col s6">Beschreibung</div>
            <div class="input-field col s6">
                <input id="icon_prefix" type="text" class="validate">
                <label for="icon_prefix">First Name</label>
            </div>
        </div>
    </div>
    <div class="col s6" id="dynamic_ai_block">
            <!--dynamischer Inhaltsblock-->
    </div>
</div>