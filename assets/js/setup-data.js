
function an_update_ui(data) {
    // do we have an error message to display?
    if (data.error.length > 0) {
        jQuery(".error-text").html(data.error);
        jQuery(".error-dialog").show();
    } else {
        jQuery(".error-dialog").hide();
    }
    if (data.info.length > 0) {
        jQuery(".info-text").html(data.info);
        jQuery(".info-dialog").show();
    } else {
        jQuery(".info-dialog").hide();
    }
    // remove any classes for non active items
    for (const tab of data.tab_list) {
        if (tab !== data.tab) {
            jQuery("#div_" + tab).hide();
            jQuery("#tab_" + tab).removeClass("nav-tab-active");
        }
    }
    // select active tab
    jQuery("#div_" + data.tab).show();
    jQuery("#tab_" + data.tab).addClass("nav-tab-active");

    // draw bar graphs
    const dateLabel = jQuery("#txtDatePicker").val();

    if (data.tab === 'searches') {
        jQuery("#search-analytics").html("");
        data.draw_graph("#search-analytics",
            'Monthly Searches in ' + dateLabel,
            'Days', 'Number of Searches', dateLabel,
            data.search_frequencies);
    }
    if (data.tab === 'keywords') {
        jQuery("#keyword-analytics").html("");
        data.draw_graph("#keyword-analytics",
            'Keyword Most often Searched for in ' + dateLabel,
            'Keyword', 'Number of Times used', dateLabel,
            data.search_keyword_frequencies);
    }
    if (data.tab === 'logs' || data.tab === 'keywords' || data.tab === 'searches') {
        jQuery(".date-picker-box").show();
    } else {
        jQuery(".date-picker-box").hide();
    }

    // enable mind-item upload button?
    jQuery(".upload-button").prop('disabled', data.file_binary_data === null || data.busy);
    jQuery(".ss-button").prop('disabled', data.busy);

    // render the inside of the mind-item table
    jQuery("#mindItemList").html(data.renderMindItemTable());

    // set mind-item pagination
    jQuery("#mindItemPagination").html(data.renderMindItemPagination());

    // mind item dialog
    if (data.mind_item_dlg_show) {
        jQuery(".qna-title").html(data.mind_item_dlg_action);
        jQuery("#qna-edit").show();
        jQuery(".mi-q1").val(data.mi_dlg_q1);
        jQuery(".mi-q2").val(data.mi_dlg_q2);
        jQuery(".mi-answer").val(data.mi_dlg_answer);
        jQuery(".mi-links").val(data.mi_dlg_links);
    } else {
        jQuery("#qna-edit").hide();
    }

    // render the inside of the synonym table
    jQuery("#synonymList").html(data.renderSynonymTable());

    // set synonym pagination
    jQuery("#synonymPagination").html(data.renderSynonymPagination());

    // mind item dialog
    if (data.synonym_dlg_show) {
        jQuery("#synonym-edit").show();
        jQuery(".synonym-title").html(data.synonym_dlg_action);
        jQuery(".syn-words").val(data.syn_dlg_words);
    } else {
        jQuery("#synonym-edit").hide();
    }


    // render the inside of the semantic table
    jQuery("#semanticList").html(data.renderSemanticTable());

    // set semantic pagination
    jQuery("#semanticPagination").html(data.renderSemanticPagination());

    // mind item dialog
    if (data.semantic_dlg_show) {
        jQuery("#semantic-edit").show();
        jQuery(".semantic-title").html(data.semantic_dlg_action);
        jQuery(".sem-word").val(data.sem_dlg_word);
        jQuery(".sem-semantic").val(data.sem_dlg_semantic);
    } else {
        jQuery("#semantic-edit").hide();
    }

    if (data.filename && data.filename.length > 0) {
        jQuery("#upload-files-label").attr("title", "you have selected \"" + data.filename + "\" for upload, please click the \"upload\" button to start this process");
        jQuery("#upload-button").attr("title", "Click this button to start uploading \"" + data.filename + "\" to SimSage");
    } else {
        jQuery("#upload-files-label").attr("title", "Click this button to select an Excel Spreadsheet for uploading to SimSage");
        jQuery("#upload-button").attr("title", "Click the \"Select Excel Spreadsheet\" button to select a Spreadsheet for uploading first");
    }

    if (data.busy) {
        jQuery("#hourglass").attr("src", image_base + '/images/hourglass.svg');
        jQuery("#busy").show();
    } else {
        jQuery("#busy").hide();
    }

}

