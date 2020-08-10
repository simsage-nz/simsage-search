<?php
/**
 * the "view" of the admin page, the HTML controls and their control bindings and form POST
 * for updating the plugin's configuration
 *
 */
?>



<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">

    <style>
        fieldset {margin-bottom: 20px;}
        .label-success { font-weight: 600; font-size: 1.1em; margin-top: 10px; margin-bottom: 20px; line-height: 20px; }
        .wide-text { width: 500px; }
        .radio_label { margin-right: 4px; font-weight: bold;}
        .tabbed-display { margin-top: 20px; margin-left: 10px; }
        .tab-disabled { color: #ccc; cursor: default; pointer-events: none; }
    </style>

    <div id="icon-themes" class="icon32"></div>
    <h2>SimSage Plugin Options</h2>

	<?php
	$active_tab = '';
	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	} // end if
	$options = get_option( PLUGIN_NAME );
    $plan = get_plan();
	// add the nonce, option_page, action and referer.
	settings_fields( PLUGIN_NAME );
	do_settings_sections( PLUGIN_NAME );

	// flags for controlling the forms and tabs
    // after signing-in we get an account set and saved locally
    $has_account = isset($options['simsage_account'] ) && isset($options['simsage_account']['id']);
    // when we have selected a site, this variable will be set
    $has_kb = $has_account && isset($options['simsage_account']['kbId']);
    // list of bot items (or initial empty array)
    $qa_list = isset($options['simsage_qa']) ? $options['simsage_qa'] : array();
    // do we have Q&A settings?
    $bot_enabled = ($plan != null);
    // do we have language customization?
    $language_enabled = ($plan != null);
    // list of synonyms
    $synonym_list = isset($options['simsage_synonyms']) ? $options['simsage_synonyms'] : array();
    // get ajax access
    $ajax_url = admin_url( 'admin-ajax.php' );
	?>

    <script lang="js">
        // set the ajax post-back url
        ajax_url = "<?php echo $ajax_url ?>";
    </script>

    <?php if ( !$has_account && ($active_tab == 'account' || $active_tab == '') ) { ?>
    <div class="label-success">
        Please enter your SimSage registration key below.<br />
        <a href="<?php echo SIMSAGE_REGO_SERVER; ?>/#/create?origin=plugin" target="_blank">Register here</a> if you don't have an account.
    </div>
	<?php } ?>

    <div id="message-area">
    </div>

    <div class="nav-tab-wrapper">
        <a href="?page=simsage-search&tab=account" class="nav-tab <?php echo ($active_tab == 'account' || $active_tab == '') ? 'nav-tab-active' : ''; ?>">Account</a>
        <a href="?page=simsage-search&tab=search" class="nav-tab <?php echo $active_tab == 'search' ? 'nav-tab-active' : ''; ?> <?php if ( ! $has_kb ) echo 'tab-disabled' ?>">Search</a>
        <?php if ( $bot_enabled ) { ?>
        <a href="?page=simsage-search&tab=bot" class="nav-tab <?php echo $active_tab == 'bot' ? 'nav-tab-active' : ''; ?> <?php if ( ! $has_kb ) echo 'tab-disabled' ?>">Bot</a>
        <?php } ?>
        <?php if ( $language_enabled ) { ?>
        <a href="?page=simsage-search&tab=synonyms" class="nav-tab <?php echo $active_tab == 'synonyms' ? 'nav-tab-active' : ''; ?> <?php if ( ! $has_kb ) echo 'tab-disabled' ?>">Synonyms</a>
        <?php } ?>
    </div>

    <?php if ( $active_tab == 'account' || $active_tab == '' ) { ?>
        <div class="tabbed-display">
        <!-- check if account has been set - in which case we have a valid setup -->
        <fieldset>
            <label>
                <input name="<?php echo PLUGIN_NAME ?>[simsage_registration_key]" type="text"
                       class="input-field" id="simsage_registration_key"
                       value="<?php echo (isset($options['simsage_registration_key']) && $options['simsage_registration_key'] != '') ? $options['simsage_registration_key'] : ''; ?>"
                       placeholder="your SimSage Registration Key"/>
                <span class="description">Please enter your SimSage Registration-key</span>
            </label>
        </fieldset>

        <fieldset>
            <label>
                <span class="description">don't have a Registration-key?&nbsp;&nbsp;<a href="<?php echo SIMSAGE_REGO_SERVER; ?>/#/create?origin=plugin" target="_blank">Register here</a></span>
            </label>
            <br /><br />
            <div>Changed plans?  Click the 'Connect to SimSage' button below to refresh.</div>
        </fieldset>

        <input type="hidden" name="action" value="sign-in">
        </div>
        <div>
            <div style="float: left; margin-right: 20px">
                <div class="button button-primary" id="sign-in-button" onclick="do_sign_in()">Connect to SimSage</div>
            </div>
            <?php if ( $has_account ) { ?>
                <div id="btnPreClose" style="float: left;">
                    <span class="button" onclick="document.getElementById('btnCloseAccount').style.display='';
                                                  document.getElementById('btnPreClose').style.display='none';">
                                                  Close my SimSage Account</span>
                </div>
            <?php } ?>
        </div>
        <div id="btnCloseAccount" style="display: none">
            <br style="clear: both; height: 20px;"/>
            <span>
                <b>CAREFUL</b>!<br/><br/>
                Are you sure you want to CLOSE your SimSage account?<br/>
                This will REMOVE all your personal data from our systems,<br/>
                We will stop charging your credit card, and it will stop this plugin from working!<br/><br/>
                <b>this action cannot be undone!</b><br/><br/>
            </span>
            <label>
                <input type="password" name="<?php echo PLUGIN_NAME ?>[simsage_password]"
                       class="input-field" id="simsage_password"
                       value=""
                       placeholder="your SimSage account password"/>
                <span class="description">Please enter your SimSage Password</span>
            </label>
            <div class="button button-secondary close-account-adjustment" onclick="do_close_account()">Close my SimSage Account</div>
        </div>

    <?php } ?>


    <?php if ($active_tab == 'search') { ?>
        <div class="tabbed-display">
            <fieldset>
                <label>
                    <input name="<?php echo PLUGIN_NAME ?>[simsage_page_size]" type="text" class="number-field" id="simsage_page_size"
                           value="<?php echo (isset($options['simsage_page_size']) && $options['simsage_page_size'] != '') ? $options['simsage_page_size'] : '3'; ?>"
                           placeholder="number of search-results per page"/>
                    <span class="description">Page size, the number of search-results per page, between <?php echo $this->get_default_field("simsage_page_size", "min") ?> and <?php echo $this->get_default_field("simsage_page_size", "max") ?> (default <?php echo $this->get_default_field("simsage_page_size", "value") ?>)</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <input name="<?php echo PLUGIN_NAME ?>[simsage_fragment_size]" type="text" class="number-field" id="simsage_fragment_size"
                           value="<?php echo (isset($options['simsage_fragment_size']) && $options['simsage_fragment_size'] != '') ? $options['simsage_fragment_size'] : '3'; ?>"
                           placeholder="number of fragments per search-result"/>
                    <span class="description">Fragment size, the number of matches shown inside each document, between <?php echo $this->get_default_field("simsage_fragment_size", "min") ?> and <?php echo $this->get_default_field("simsage_fragment_size", "max") ?> (default <?php echo $this->get_default_field("simsage_fragment_size", "value") ?>)</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <input name="<?php echo PLUGIN_NAME ?>[simsage_word_distance]" type="text" class="number-field" id="simsage_word_distance"
                           value="<?php echo (isset($options['simsage_word_distance']) && $options['simsage_word_distance'] != '') ? $options['simsage_word_distance'] : '20'; ?>"
                           placeholder="maximum distance between keywords"/>
                    <span class="description">Maximum distance between keywords between <?php echo $this->get_default_field("simsage_word_distance", "min") ?> and <?php echo $this->get_default_field("simsage_word_distance", "max") ?> (use 0 for document level search, default <?php echo $this->get_default_field("simsage_word_distance", "value") ?>)</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <span class="radio_label">no</span><input name="<?php echo PLUGIN_NAME ?>[simsage_override_default_search]" type="radio" value="0" <?php echo (!isset($options['simsage_override_default_search']) || $options['simsage_override_default_search'] != '1') ? 'checked' : ''; ?> />
                    <span class="radio_label">yes</span><input name="<?php echo PLUGIN_NAME ?>[simsage_override_default_search]" type="radio" value="1" <?php echo (isset($options['simsage_override_default_search']) && $options['simsage_override_default_search'] == '1') ? 'checked' : ''; ?> />
                    <span class="description">Override the default WordPress Search.</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <span class="radio_label">no</span><input name="<?php echo PLUGIN_NAME ?>[simsage_ask_email]" type="radio" value="0" <?php echo (!isset($options['simsage_ask_email']) || $options['simsage_ask_email'] != '1') ? 'checked' : ''; ?> />
                    <span class="radio_label">yes</span><input name="<?php echo PLUGIN_NAME ?>[simsage_ask_email]" type="radio" value="1" <?php echo (isset($options['simsage_ask_email']) && $options['simsage_ask_email'] == '1') ? 'checked' : ''; ?> />
                    <span class="description">Ask a user for their email address if there is no answer and/or search results.</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <span class="radio_label">no</span><input name="<?php echo PLUGIN_NAME ?>[simsage_adv_filter]" type="radio" value="0" <?php echo (isset($options['simsage_adv_filter']) && $options['simsage_adv_filter'] == '0') ? 'checked' : ''; ?> />
                    <span class="radio_label">yes</span><input name="<?php echo PLUGIN_NAME ?>[simsage_adv_filter]" type="radio" value="1" <?php echo (!isset($options['simsage_adv_filter']) || $options['simsage_adv_filter'] != '0') ? 'checked' : ''; ?> />
                    <span class="description">Show the advanced search filter button.</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <span class="radio_label">no</span><input name="<?php echo PLUGIN_NAME ?>[use_operator]" type="radio" value="0" <?php echo (isset($options['use_operator']) && $options['use_operator'] == '0') ? 'checked' : ''; ?> />
                    <span class="radio_label">yes</span><input name="<?php echo PLUGIN_NAME ?>[use_operator]" type="radio" value="1" <?php echo (!isset($options['use_operator']) || $options['use_operator'] != '0') ? 'checked' : ''; ?> />
                    <span class="description">Operator assistance is provided for users (available only when you are)</span>
                </label>
            </fieldset>
            <input type="hidden" name="action" value="update-search">
            </div>
            <?php submit_button( 'update Search Settings', 'primary','submit', true ); ?>

        <?php } ?>


        <?php if ($active_tab == 'bot') { ?>
            <div class="tabbed-display">
            <fieldset>
                <label>
                    <span class="radio_label">no</span><input name="<?php echo PLUGIN_NAME ?>[simsage_use_bot]" type="radio" value="0" <?php echo (isset($options['simsage_use_bot']) && $options['simsage_use_bot'] == '0') ? 'checked' : ''; ?> />
                    <span class="radio_label">yes</span><input name="<?php echo PLUGIN_NAME ?>[simsage_use_bot]" type="radio" value="1" <?php echo (!isset($options['simsage_use_bot']) || $options['simsage_use_bot'] != '0') ? 'checked' : ''; ?> />
                    <span class="description">Use the SimSage bot along with search.</span>
                </label>
            </fieldset>

            <fieldset>
                <label>
                    <input name="<?php echo PLUGIN_NAME ?>[bot_threshold]" type="text" class="number-field" id="bot_threshold"
                           value="<?php echo (isset($options['bot_threshold']) && $options['bot_threshold'] != '') ? $options['bot_threshold'] : '0.8125'; ?>"
                           placeholder="maximum distance between keywords"/>
                    <span class="description">SimSage bot threshold, a number between <?php echo $this->get_default_field("bot_threshold", "min") ?> and <?php echo $this->get_default_field("bot_threshold", "max") ?> setting the accuracy of the bot (default <?php echo $this->get_default_field("bot_threshold", "value") ?>)</span>
                </label>
            </fieldset>

            <br/>
            <h2>Question and Answer pairs</h2>

            <?php
                foreach ($qa_list as $qa) {
            ?>
                    <fieldset>
                        <label>
                            <input name="<?php echo PLUGIN_NAME ?>[simsage_qa][<?php echo $qa["id"] ?>][question]" type="text" class="input-field"
                                   value="<?php echo $this->remove_esc($qa["question"]); ?>"
                                   maxlength="<?php echo MAX_STRING_LENGTH; ?>"
                                   placeholder="a single Question"/>
                        </label>
                        <label>
                            <input name="<?php echo PLUGIN_NAME ?>[simsage_qa][<?php echo $qa["id"] ?>][answer]" type="text" class="input-field"
                                   maxlength="<?php echo MAX_STRING_LENGTH; ?>"
                                   value="<?php echo $this->remove_esc($qa["answer"]); ?>"
                                   placeholder="it's Answer"/>
                        </label>
                        <label>
                            <input name="<?php echo PLUGIN_NAME ?>[simsage_qa][<?php echo $qa["id"] ?>][context]" type="text" class="input-field"
                                   maxlength="<?php echo MAX_STRING_LENGTH; ?>"
                                   value="<?php echo $this->remove_esc($qa["context"]); ?>"
                                   placeholder="optional matching-context"/>
                        </label>
                        <?php submit_button( 'remove ' . $qa["id"], 'secondary','submit', false); ?>
                    </fieldset>
            <?php
                }
            ?>
            <?php submit_button( 'add', 'secondary','submit', true ); ?>
            <input type="hidden" name="action" value="update-bot">
        </div>
        <?php submit_button( 'update Bot Settings', 'primary','submit', true ); ?>

    <?php } ?>


    <?php if ($active_tab == 'synonyms') { ?>
        <div class="tabbed-display">
            <?php
                foreach ($synonym_list as $synonym) {
            ?>
                <fieldset>
                    <label>
                        <input name="<?php echo PLUGIN_NAME ?>[simsage_synonyms][<?php echo $synonym["id"] ?>][words]" type="text" class="input-field wide-text"
                               value="<?php echo $this->remove_esc($synonym["words"]); ?>"
                               maxlength="<?php echo MAX_STRING_LENGTH; ?>"
                               placeholder="comma separated list of Synonyms"/>
                    </label>
                    <?php submit_button( 'remove ' . $synonym["id"], 'secondary','submit', false); ?>
                </fieldset>
            <?php
                }
            ?>
            <?php submit_button( 'add', 'secondary','submit', true ); ?>
            <input type="hidden" name="action" value="update-synonyms">
        </div>
        <?php submit_button( 'update Synonyms', 'primary','submit', true ); ?>

    <?php } ?>


</div>

