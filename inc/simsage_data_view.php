<?php
/**
 * the "view" of the SimSage analytics page
 */
?>

<?php
$plan = simsage_get_plan();
$has_access = ($plan != null && isset( $plan['analyticsWindowInMonths'] ) && $plan['analyticsWindowInMonths'] > 0);
?>

<script lang="js">
    // set an image base for all our templates to use (url bases for images)
    image_base = "<?php echo sanitize_text_field($this->asset_folder) ?>";
    server = "<?php echo sanitize_text_field($this->get_account_setting("server")) ?>";

    // the settings for this application - no trailing / on the base_url please

    settings = {
        // the service layer end-point, set after logging in
        base_url: server,
        // api version of ws_base
        api_version: 1,
        // the organisation's id to search
        organisationId: "<?php echo sanitize_text_field($this->get_account_setting("id")) ?>",
        // the knowledge base's Id (selected site) and security id (sid)
        kbId: "<?php echo sanitize_text_field($this->get_site_setting("kbId")) ?>",

        // the operator uses the SecurityID to verify themselves, do not expose it to your web users!
        sid: "<?php echo sanitize_text_field($this->get_site_setting("sid")) ?>",
        // valid image types
        image_types: [".jpg", ".jpeg", ".png", ".gif", ".svg"]
    };

</script>


<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">

    <style>
        .tab-cursor { cursor: pointer; }
    </style>

    <div class="analytics-area">
        <h2>SimSage Data</h2>
    </div>

    <?php
    // set when connected to SimSage
    $has_sites = (simsage_get_kb() != null);
    ?>

    <?php if ( $has_sites && $has_access ) { ?>

    <div class="analytics-area">

        <div id="busy" class="busy" style="display: none;">
            <div class="busy-image"><img id="hourglass" src="" alt="hourglass" class="busy-image-size"></div>
        </div>

        <!-- error message display bar -->
        <div class="error-dialog">
            <span class="close-button" onclick="this.parentElement.style.display='none'; data.close_error();">&times;</span>
            <div class="error-text"></div>
        </div>

        <!-- info message display bar -->
        <div class="info-dialog">
            <span class="close-button" onclick="this.parentElement.style.display='none'; data.close_error();">&times;</span>
            <div class="info-text"></div>
        </div>

        <div class="date-picker-box">
            <label>
                <input type="text" id="txtDatePicker" class="datepicker tab-cursor" name="datepicker" value="" readonly />
            </label>
            <button onclick="data.getAnalytics()" class="button" title="Reload/Refresh statistical data">refresh</button>
        </div>

        <h2 class="nav-tab-wrapper">
            <span id="tab_keywords" onclick="data.select_tab('keywords')" class="nav-tab tab-cursor nav-tab-active">Keywords</span>
            <span id="tab_searches" onclick="data.select_tab('searches')" class="nav-tab tab-cursor">Search Access</span>
            <span id="tab_logs" onclick="data.select_tab('logs')" class="nav-tab tab-cursor">Download</span>
            <span id="tab_qna" onclick="data.select_tab('qna')" class="nav-tab tab-cursor">Q&A</span>
            <span id="tab_synonyms" onclick="data.select_tab('synonyms')" class="nav-tab tab-cursor">Synonyms</span>
            <span id="tab_semantics" onclick="data.select_tab('semantics')" class="nav-tab tab-cursor">Semantics</span>
        </h2>

        <!-- graphical display items -->
        <div id='layout'>

            <!-- keyword analytics tab -->
            <div id='div_keywords' class="container">
                <svg id="keyword-analytics" />
            </div>

            <!-- search analytics tab -->
            <div id='div_searches' class="container" style="display: none;">
                <svg id="search-analytics" />
            </div>

        </div>

        <!-- download tab -->
        <div id='div_logs' style="display: none;">
            <div class="button-row">
                <button onclick="data.dlOperatorConversations()" class="button button-style ss-button">Operator Conversation Spreadsheet</button>
                <span class="button-help-text">Download a Spreadsheet containing all conversations between Operators and Clients for the selected month.</span>
            </div>
            <div class="button-row">
                <button onclick="data.dlQueryLog()" class="button button-style ss-button">Search & Query Log Spreadsheet</button>
                <span class="button-help-text">Download a log of what people have been searching / asking on this site for the selected month.</span>
            </div>
            <div class="button-row">
                <button onclick="data.dlLanguageCustomizations()" class="button button-style ss-button" title="Download a Spreadsheet of all QA Pairs and Language Customizations">Content Spreadsheet</button>
                <span class="button-help-text">Download a SimSage QA / language Spreadsheet containing all your customized content (not month specific).</span>
            </div>
            <div class="button-row">
                <button onclick="data.dlContentAnalysis()" class="button button-style ss-button" title="Download a Content Analysis Spreadsheet">Content Analysis Spreadsheet</button>
                <span class="button-help-text">Download a Spreadsheet containing all currently crawled content and a Semantic analysis for each item (not month specific).</span>
            </div>
        </div>

        <!-- Q&A editor / adder -->
        <div id="qna-edit" class="qna-editor" style="display: none;">
            <div class="qna-title"></div>
            <div class="qna-control"><label class="qna-label">question <br/><input class="input-text mi-q1" type="text" alt="question" title="question" placeholder="question" /></label></div>
            <div class="qna-control"><label class="qna-label">alternative <br/><input class="input-text mi-q2" type="text" alt="alternative" title="alternative question (optional)" placeholder="alternative question (optional)" /></label></div>
            <div class="qna-control"><label class="qna-label">answer text<br/>
                    <textarea rows="3" class="mi-answer" cols="50" title="the answer" placeholder="the answer"></textarea>
                </label>
            </div>
            <div class="qna-control"><label class="qna-label">links<br/>
                    <textarea rows="3" cols="50" class="mi-links" title="links" placeholder="links"></textarea>
                </label>
            </div>
            <div class="qna-buttons-container">
                <div class="qna-buttons">
                    <button class="ss-button" onclick="data.mindItemDialogClose()">cancel</button>
                    <button class="ss-button" onclick="data.mindItemDialogSave()">save</button>
                </div>
            </div>
        </div>

        <!-- Q&A tab -->
        <div id='div_qna' class="qna" style="display: none;">
            <div class="simsage-find-box">
                <div class="find-label">find items in the mind</div>
                <div class="find-dialog">
                    <label><input type="text" value="" spellcheck="false" autoFocus class="find-text-style"
                           onKeyUp="data.handleMindItemKey(event.keyCode)" onChange="data.setMindItemFilter(event.target.value)" /></label>
                </div>
                <div class="find-image-box ss-button">
                    <img class="find-image ss-button"
                         onClick="data.getMindItems()"
                         src="<?php echo $this->asset_folder . 'images/dark-magnifying-glass.svg'?>" title="search" alt="search"/>
                </div>
            </div>
            <div>
                <table class="simsage-find-table">
                    <thead>
                        <tr class="table-header">
                            <td class="id-column">id</td>
                            <td class="question-column">question</td>
                            <td class="action-column">actions</td>
                        </tr>
                    </thead>
                    <tbody id="mindItemList">
                    </tbody>
                    <tr class="pagination-bar-tr">
                        <td colspan="3">
                            <div id="mindItemPagination" class="pagination-bar"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bottom-action-bar">
                            <span class="upload-control">
                                    <label id="upload-files-label" for="upload-files" class="button ss-button">Select Excel Spreadsheet</label>
                                    <input id="upload-files" type="file" accept=".xls,.xlsx" style="display: none;" onchange="data.handleUploadChange(event)" /></label>
                                <button id="upload-button" class="button upload-button" disabled onClick="data.uploadMindItems()">upload</button>
                            </span>
                            <span>
                                <button class="button export-button" title="export existing SimSage Q&A and Synonym information" onClick="data.dlLanguageCustomizations()">export</button>
                            </span>
                            <span class="delete-button ss-button" title="delete all mind-items" onClick="data.deleteAllMindItems()">
                                <img src="<?php echo $this->asset_folder . 'images/delete.svg'?>" class="delete-button-image" alt="delete" />
                            </span>
                            <span class="add-button ss-button" title="add a new mind-item" onClick="data.addMindItem()">
                                <img src="<?php echo $this->asset_folder . 'images/add.svg'?>" class="add-button-image" alt="add" />
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Synonym editor / adder -->
        <div id="synonym-edit" class="synonym-editor" style="display: none;">
            <div class="synonym-title"></div>
            <div class="synonym-control">
                <label class="synonym-label">synonyms <br/>
                    <textarea rows="14" class="syn-words" cols="50" title="synonyms separated by commas" placeholder="synonyms separated by commas"></textarea>
                </label>
            </div>
            <div class="synonym-buttons-container">
                <div class="synonym-buttons">
                    <button class="ss-button" onclick="data.synonymDialogClose()">cancel</button>
                    <button class="ss-button" onclick="data.synonymDialogSave()">save</button>
                </div>
            </div>
        </div>

        <!-- Synonyms tab -->
        <div id='div_synonyms' class="qna" style="display: none;">
            <div class="simsage-find-box">
                <div class="find-label">find synonyms</div>
                <div class="find-dialog">
                    <label><input type="text" value="" spellcheck="false" autoFocus class="find-text-style"
                                  onKeyUp="data.handleSynonymKey(event.keyCode)" onChange="data.setSynonymFilter(event.target.value)" /></label>
                </div>
                <div class="find-image-box ss-button">
                    <img class="find-image ss-button"
                         onClick="data.getSynonyms()"
                         src="<?php echo $this->asset_folder . 'images/dark-magnifying-glass.svg'?>" title="search" alt="search"/>
                </div>
            </div>
            <div>
                <table class="simsage-find-table">
                    <thead>
                    <tr class="table-header">
                        <td class="id-column">id</td>
                        <td class="question-column">synoynms</td>
                        <td class="action-column">actions</td>
                    </tr>
                    </thead>
                    <tbody id="synonymList">
                    </tbody>
                    <tr class="pagination-bar-tr">
                        <td colspan="3">
                            <div id="synonymPagination" class="pagination-bar"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bottom-action-bar">
                            <span class="add-button ss-button" title="add a new synonym" onClick="data.addSynonym()">
                                <img src="<?php echo $this->asset_folder . 'images/add.svg'?>" class="add-button-image" alt="add" />
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Semantic editor / adder -->
        <div id="semantic-edit" class="semantic-editor" style="display: none;">
            <div class="semantic-title"></div>
            <div class="semantic-control">
                <label class="sem-label">word <br/>
                    <input class="input-text sem-word" type="text" alt="word" title="word" placeholder="word" />
                </label>
            </div>
            <div class="semantic-control">
                <label class="sem-label">semantic <br/>
                    <input class="input-text sem-semantic" type="text" alt="semantic" title="semantic" placeholder="semantic" />
                </label>
            </div>
            <div class="semantic-buttons-container">
                <div class="semantic-buttons">
                    <button class="ss-button" onclick="data.semanticDialogClose()">cancel</button>
                    <button class="ss-button" onclick="data.semanticDialogSave()">save</button>
                </div>
            </div>
        </div>

        <!-- Semantics tab -->
        <div id='div_semantics' class="qna" style="display: none;">
            <div class="simsage-find-box">
                <div class="find-label">find semantics</div>
                <div class="find-dialog">
                    <label><input type="text" value="" spellcheck="false" autoFocus class="find-text-style"
                                  onKeyUp="data.handleSemanticKey(event.keyCode)" onChange="data.setSemanticFilter(event.target.value)" /></label>
                </div>
                <div class="find-image-box ss-button">
                    <img class="find-image ss-button"
                         onClick="data.getSemantics()"
                         src="<?php echo $this->asset_folder . 'images/dark-magnifying-glass.svg'?>" title="search" alt="search"/>
                </div>
            </div>
            <div>
                <table class="simsage-find-table">
                    <thead>
                    <tr class="table-header">
                        <td class="sem-id-column">word</td>
                        <td class="sem-question-column">semantic</td>
                        <td class="action-column">actions</td>
                    </tr>
                    </thead>
                    <tbody id="semanticList">
                    </tbody>
                    <tr class="pagination-bar-tr">
                        <td colspan="3">
                            <div id="semanticPagination" class="pagination-bar"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="bottom-action-bar">
                            <span class="add-button ss-button" title="add a new semantic" onClick="data.addSemantic()">
                                <img src="<?php echo $this->asset_folder . 'images/add.svg'?>" class="add-button-image" alt="add" />
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>



    </div>

    <?php } else if ( !$has_access ) { ?>
    <div class="operator-area">
        <div class="label-success">Your plan does not provide you with Analytics.  Please <a href="<?php echo $this->get_portal_server(); ?>/#/sign-in?origin=plugin" target="_blank">upgrade your plan</a> if you wish to have access to analytics.</div>
    </div>
    <?php } else { ?>
        <div class="analytics-area">
            <div class="label-success">Please <a href="/wp-admin/options-general.php?page=simsage-search">configure</a> your SimSage plugin first.</div>
        </div>
    <?php } ?>

</div>
