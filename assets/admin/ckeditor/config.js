/**
 * @license Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
CKEDITOR.editorConfig = function( config ) {
  
    config.language = 'vi';
    config.width = 'auto';
    config.height = 510;
    config.skin = (!window.Helpers.isDarkStyle())?'moono-lisa':'moono-dark';
    config.entities = false;
    config.entities_latin = false;
    config.entities_greek = false;
    config.basicEntities = false;
    config.contentsCss = ASSET+((!window.Helpers.isDarkStyle())?'assets/admin/ckeditor/contents.css':'assets/admin/ckeditor/contents_dark.css');
    config.pasteFromWordRemoveFontStyles = false;
    config.extraPlugins ='youtube,lineheight,image2,ckeditorfa,tableresize';
    config.line_height="1;1.1;1.2;1.3;1.4;1.5;2;2.5;3;3.5;4;4.5;5";
    config.allowedContent = true;
    config.removePlugins = 'image,exportpdf,uploadimage,BBCode,language,ckeditorfa';
    config.pasteFromWordRemoveStyles = false;
    config.codemirror = {theme: 'default', lineNumbers: true, lineWrapping: true, matchBrackets: true, autoCloseTags: true, autoCloseBrackets: true, enableSearchTools: true, enableCodeFolding: true, enableCodeFormatting: true, autoFormatOnStart: true, autoFormatOnModeChange: true, autoFormatOnUncomment: true, mode: 'htmlmixed', showSearchButton: true, showTrailingSpace: true, highlightMatches: true, showFormatButton: true, showCommentButton: true, showUncommentButton: true, showAutoCompleteButton: true, styleActiveLine: true};
    config.filebrowserBrowseUrl = ASSET+'admin/ckfinder';
    config.filebrowserUploadUrl =ASSET+'assets/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.stylesSet = [{ name: 'Font Seguoe Regular', element: 'span', attributes: { class: 'segui' } }, { name: 'Font Seguoe Semibold', element: 'span', attributes: { class: 'seguisb' } }, { name: 'Italic title', element: 'span', styles: { 'font-style': 'italic' } }, {name: 'Special Container', element: 'div',styles: { background: '#eee', border: '1px solid #ccc', padding: '5px 10px' }}, { name: 'Big', element: 'big' }, { name: 'Small', element: 'small' }, { name: 'Inline ', element: 'q' }, { name: 'marker', element: 'span', attributes: { class: 'marker' } }];
    /* Config Wordcount */
    config.versionCheck = false;
    config.wordcount = {
        showParagraphs: true,
        showWordCount: true,
        showCharCount: true,
        countSpacesAsChars: false,
        countHTML: false,
        filter: new CKEDITOR.htmlParser.filter({
            elements: {
                div: function (element) {
                    if (element.attributes.class == 'mediaembed') {
                        return false;
                    }
                }
            }
        })
    };
};

