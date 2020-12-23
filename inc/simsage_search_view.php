<?php
/**
 * SimSage Search Box for rendering
 */
?>

<script lang="js">
    // set an image base for all our templates to use (url bases for images)
    image_base = "<?php echo sanitize_text_field($this->asset_folder) ?>";
    server = "<?php echo sanitize_text_field($this->get_account_setting("server")) ?>";

    // the settings for this application - no trailing / on the base_url please
    // it is imperative that we do not expose the SID here (securityID), as it is what protects your account from malicious use
    settings = {
        // the service layer end-point, change "<server>" to ... (no / at end)
        base_url: server,
        // api version of ws_base
        api_version: 1,
        // the organisation's id to search - all sanitized
        organisationId: "<?php echo sanitize_text_field($this->get_account_setting("id")) ?>",
        // the knowledge base's Id (selected site) and security id (sid)
        kbId: "<?php echo sanitize_text_field($this->get_site_setting("kbId")) ?>",

        // do we have an operator by plan?
        operator_enabled: <?php echo $this->get_plan_boolean_value("operatorEnabled", true) ?>,

        category_size: 5,       // size of category lists
        page_size: 5,           // number of pages per page in search
        page_size_custom: 10,   // number of pages per page in custom view
        currency_symbol: "$",

        // search settings
        fragment_count: <?php echo $this->get_user_value("simsage_fragment_size", 3) ?>,
        max_word_distance: <?php echo $this->get_user_value("simsage_word_distance", 20) ?>,
        use_spelling_suggest: false,
        context_label: "<?php echo sanitize_text_field($this->context) ?>",
        context_match_boost: <?php echo sanitize_text_field($this->context_boost) ?>,
        // QA sensitivity - controls the A.I's replies - we suggest you don't change it!
        bot_threshold: <?php echo $this->get_user_value("bot_threshold", 0.8125) ?>,
        // show the advanced filters?
        show_advanced_filter: <?php echo $this->get_user_boolean_value("simsage_adv_filter", true) ?>,
        // image types for link name display
        image_types: [".jpg", ".jpeg", ".png", ".gif", ".svg"],
        // placeholder for search
        search_placeholder: "",
    };

    // setup search
    let search = search_control.instantiate();

    // init when ready
    jQuery(document).ready(function () {
        search.init();
    })

</script>

<div class="search-bar">

    <!-- search options button, menu and chevron -->
    <div class="search-options-button no-select" title="Search options" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.toggle_menu()">
        <div class="search-options-button-inner">
            <span class="search-options-text">Search options</span>
            <!-- hidden needed by SimSage logic -->
            <input type="hidden" class="time-stamp" value="0" />
            <div class="search-options-chevron-image-box">
                <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/chevron-down.svg'?>" alt="select" class="search-options-chevron-image">
            </div>
        </div>

        <!-- ************************** -->
        <!-- advanced search filter box -->
        <div class="filter-box-view" style="display: none;" onclick="search.nop()" >
            <div class="filter-box" >
                <div class="speech-bubble">
                    <div class="title">
                        <span class="search-by-text" title="Search by:">SEARCH BY:</span>
                        <span onclick="search.hide_menu(null)" title="Close" class="close-box">
                            <img tabindex="0" onkeyup="if (activation(event)) search.hide_menu(event)" src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
                            <span class="close-text">Close</span>
                        </span>
                    </div>
                    <table class="filter-table">
                        <tr class="tr-1">
                            <td class="col-1 sign-in-box" style="display: none;">
                                <span class="category-text">Sign-in</span>
                                <div class="category-item">
                                    <label class="sign-in-sel">
                                        <select name="sign-in" class="category-select dd-sign-in" tabindex="0"
                                                onchange="search.do_change_domain()">
                                        </select>
                                    </label>
                                </div>
                            </td>
                            <td class="col-2 sign-in-text" style="display: none;">
                                        <span class="clear-text" title="sign-in" tabindex="0"
                                              onkeyup="if (activation(event)) this.onclick(null)" onclick="search.show_sign_in()">sign-in</span>
                            </td>
                            <td class="col-2 sign-out-text" style="display: none;">
                                <span class="clear-text" title="sign-out" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.do_sign_out()">sign-out</span>
                            </td>

                            <td class="col-1">
                                <span class="category-text">Document Type</span>
                                <div class="category-item">
                                    <label class="document-type-sel">
                                        <select name="document-type" class="category-select" onchange="" tabindex="0">
                                            <option value="">All Document Types</option>
                                            <option value="html,htm">Web</option>
                                            <option value="doc,docx">Word</option>
                                            <option value="pdf">PDF</option>
                                            <option value="xls,xlsx">Excel</option>
                                            <option value="jpg,jpeg,png,gif">Images</option>
                                        </select>
                                    </label>
                                </div>
                            </td>
                            <td class="col-2">
                                <span class="clear-text" title="Clear Document Type" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.reset_document_type()">Clear</span>
                            </td>

                            <td class="col-1 knowledge-base-selector" style="display: none;">
                                <span class="category-text">Knowledge Base</span>
                                <div class="category-item">
                                    <label class="knowledge-base-sel">
                                        <select name="knowledge-base" class="category-select dd-knowledge-base" tabindex="0"
                                                onchange="search.on_change_kb()">
                                        </select>
                                    </label>
                                </div>
                            </td>
                            <td class="col-2 knowledge-base-selector" style="display: none;">
                            </td>

                            <td class="col-1" colspan="2">
                                <span class="category-text">Source</span>
                                <div class="category-item">
                                    <label class="source-sel">
                                        <select name="source" class="category-select dd-source" onchange="search.on_change_source()" tabindex="0">
                                        </select>
                                    </label>
                                </div>
                            </td>

                            <td class="col-1" colspan="2">
                                <span class="category-text">Title</span>
                                <div class="category-item">
                                    <label>
                                        <input type="text" autocomplete="false" value="" maxlength="100" placeholder="Enter Title" class="category-input title-text" title="Enter Title">
                                    </label>
                                </div>
                            </td>

                            <td class="col-1" colspan="2">
                                <span class="category-text">URL</span>
                                <div class="category-item">
                                    <label>
                                        <input type="text" value="" autocomplete="false" maxlength="100" placeholder="Enter URL" class="category-input url-text" title="Enter URL">
                                    </label>
                                </div>
                            </td>

                            <td class="col-1" colspan="2">
                                <span class="category-text">Author</span>
                                <div class="category-item">
                                    <label>
                                        <input type="text" value="" autocomplete="false" maxlength="100" placeholder="Enter Author" class="category-input author-text" title="Enter Author">
                                    </label>
                                </div>
                            </td>

                        </tr>
                    </table>
                </div>
                <div class="bar-bottom">
                    <span class="clear-text-box">
                        <span class="clear-all-text" title="Clear all" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.clear_all()">Clear all</span>
                    </span>
                    <span class="done-button" title="Done" onclick="search.hide_menu()">
                        <span tabindex="0" onkeyup="if (activation(event)) search.hide_menu(event)" class="done-text">Done</span>
                    </span>
                </div>
            </div>
        </div>
    </div>


    <!-- search box -->
    <div class="search-box-container">
        <div class="search-text-box">
            <label class="search-text-label" title="ask SimSage">
                <input type="text" value="" autocomplete="false" class="search-text" maxlength="100" onkeyup="search.search_typing(event)">
            </label>
        </div>

        <!-- search clear (cross) -->
        <div class="clear-search-button-box" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" title="clear your query" onclick="search.clear_search()">
            <span class="search-button-span-box">
                <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close-solid.svg'?>" alt="select" class="clear-button-image">
            </span>
        </div>

        <!-- search button -->
        <div class="search-button-box" title="query SimSage" onclick="search.do_search()">
            <span class="search-button-box-text">
                SEARCH
            </span>
        </div>
    </div>
</div>

<!-- remove float left -->
<br clear="both" />

<!-- ************************** -->
<!-- bot reply speech bubble -->
<div class="bot-box-view" style="display: none;">
    <div class="bot-box" >
        <div class="speech-bubble">
                <span onclick="search.close_bot()" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" title="Close" class="close-box">
                    <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
                </span>
            <div class="title">
                    <span class="bot-image-box">
                        <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/person.svg'?>" class="bot-image" alt="bot" />
                    </span>
                <span class="bot-label-text" title="Bot">Bot,&nbsp; date-time</span>
            </div>
            <div class="bot-text">
                <span class="bot-reply-text"></span>
            </div>
            <div class="bot-links">
            </div>
        </div>
    </div>
</div>

<div class="dialog-parent">

    <!-- ************************** -->
    <!-- spelling suggest speech bubble -->
    <div class="spelling-box-view" style="display: none;">
        <div class="spelling-box" >
            <div class="speech-bubble">
                <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_spelling_suggestion()" title="Close" class="close-box">
                    <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
                </span>
                <div class="title">
                    <span class="spelling-label-text" title="Did you mean...">Did you mean, ?</span>
                </div>
                <div class="button-box">
                    <button class="yes-button" value="yes" title="yes" onclick="search.use_spelling_suggestion()">yes</button>
                    <button class="no-button" value="no" title="no" onclick="search.close_spelling_suggestion()">no</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ***************** -->
    <!-- Error dialog box -->
    <div class="error-dialog-box" style="display: none;">
        <div class="error-dialog" >
            <div class="header">
                <span class="title" title="an error occurred">ERROR</span>
                <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_error()" title="Close" class="close-box">
                    <span class="close-text">Close</span>
                    <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
                </span>
            </div>
            <div class="error-text"></div>
            <div class="error-spacer"></div>
        </div>
    </div>


    <!-- ***************** -->
    <!-- Sign-in dialog box -->
    <div class="search-sign-in" style="display: none;">
        <div class="search-sign-in-area">
            <div class="header">
                <span class="title sign-in-title" title="Sign-in">sign-in</span>
                <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_sign_in()" title="Close" class="close-box">
                <span class="close-text">Close</span>
                <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
            </span>
            </div>
            <span class="category-text">username</span>
            <div class="category-item">
                <label>
                    <input type="text" maxlength="100" placeholder="your username" class="category-input sign-in-user-name" title="your username">
                </label>
            </div>
            <span class="category-text">password</span>
            <div class="category-item">
                <label>
                    <input type="password" maxlength="80" placeholder="your password" class="category-input sign-in-password" title="your password">
                </label>
            </div>
            <div class="spacer"></div>
        </div>
        <div class="bar-bottom">
            <span class="clear-text-box">
            </span>
            <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" class="sign-in-button" title="sign-in" onclick="search.do_sign_in()">
                <span class="sign-in-text">sign-in</span>
            </span>
        </div>
    </div>


    <!-- ***************** -->
    <!-- NO Search results -->
    <div class="no-search-results" style="display: none;">
        <div class="no-results-found-box">
            <span class="no-results-found">No Results found for</span>
            <span class="not-found-words">&nbsp;</span>
            <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_no_search_results()" title="Close" class="close-box">
            <span class="close-text">Close</span>
            <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
        </span>
        </div>
        <div class="ask-email-box">
            <div class="no-results-text">
                If you would like us to follow up on your query by email, please enter your email address and we'll
                get back to you with a response within 24 hours
            </div>
            <!-- email text box -->
            <div>
                <span class="email-text-box">
                    <label class="email-address-text">
                        <input type="text" placeholder="Enter your email address" title="Enter your email address"
                               maxlength="100" onkeypress="search.email_typing(event)" class="email-text">
                    </label>
                </span>
                <span class="send-button-disabled float-left email-send-button" title="Send" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.do_email()">
                    <span class="send-text">Send</span>
                </span>
            </div>
        </div>
        <div class="ask-emailed-box" style="display: none;">
            <div class="no-results-text">
                We have emailed support and will endeavor to get back to you with a response within 24 hours
            </div>
        </div>
        <div class="chat-spacer"></div>
    </div>

    <!-- ****************************** -->
    <!-- Detail view of a specific item -->
    <div class="search-details-view" style="display: none;">
        <div class="search-details" >
            <div class="search-details-header">
                <span class="details-text" title="details">DETAILS</span>
                <span tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_search_details()" title="Close" class="close-box">
                <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" />
                </span>
                <span onclick="search.close_search_details()" title="Close" class="close-box">
                    <span class="close-text">Close</span>
                </span>
            </div>
            <div class="spacer"></div>
            <div class="detail-table">
            </div>
        </div>
    </div>

</div>


<!-- ********************* -->
<!-- Search result display -->
<div class="search-results" style="float: left;">

    <div class="pagination-box search-display" style="display: none;">
    </div>

    <div class="search-results-two-columns search-display">
        <!-- this is where the search results are rendered into -->
        <div class="search-results-td search-results-text">
        </div>

        <div class="search-results-td search-results-images" style="display: none;">
            <div class="search-result-images">
            </div>
        </div>

    </div>

    <!-- this is where the categories of semantics go -->
    <div class="category-items-td">
    </div>

</div>

<br clear="both" />

<!-- ************************************ -->
<!-- chat dialog opened by the button below -->

<div class="operator-chat-box-view" onclick="search.nop()" style="display: none;">
    <div class="operator-chat-box">
        <div class="operator-close-width">
            <div class="div-close" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.close_chat()" title="Close chat">
                <span class="close-text">Close</span>
                <span class="close-image-box"><img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/close.svg'?>" class="close-image" alt="close" /></span>
            </div>
            <div class="chat-table">
            </div>
        </div>
        <div class="chat-bar-bottom">
                <span class="chat-text-box" onclick="search.focus_text('.chat-text')">
                    <label class="chat-box-text">
                        <input type="text" value="" autocomplete="false" placeholder="Type your message" class="chat-text" title="Type your message"
                               maxlength="200" onkeyup="search.chat_typing(event)">
                    </label>
                </span>
            <span class="chat-button-disabled chat-button-control" title="Send" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.do_chat()">
                <span class="chat-send-text">Send</span>
            </span>
        </div>
    </div>
</div>

<!-- ************************************ -->
<!-- chat with us button at the right-hand-side bottom -->

<div class="chat-button-at-bottom" style="display: none;">
    <!-- chat with us button online -->
    <div class="chat-container online" tabindex="0" onkeyup="if (activation(event)) this.onclick(null)" onclick="search.show_chat()">
            <span class="chat-with-us-online" title="Chat with us">
                <span class="chat-with-us-image-box">
                    <img src="<?php echo sanitize_text_field($this->asset_folder) . 'images/chat-white.svg'?>" alt="select" class="chat-with-us-image">
                </span>
                <span class="chat-with-us-text chat-with-us-text-box-online">
                    Chat with us
                </span>
            </span>
    </div>
</div>

