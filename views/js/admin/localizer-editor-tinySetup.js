window.tinyMCEPreInit = {};
window.tinyMCEPreInit.base = localizer_moduleUrl + 'views/libs/tinymce';
window.tinyMCEPreInit.suffix = '.min';
$('head').append($('<script>').attr('type', 'text/javascript').attr('src', localizer_moduleUrl + 'views/libs/tinymce/tinymce.min.js'));

$(document).ready(function(){
    tinySetup(null);
    tinymce.remove();
    displayTinyMCE();
    /*$(this).ajaxComplete(function(ev, jqXHR, settings) {
        if (!is_prestashop_default_controller && settings.url.toLowerCase().indexOf("notifications") == -1) {
            tinySetup(null);
            tinymce.remove();
            displayTinyMCE();
        }
    });*/
});

function tinySetup(config){
    return true;
}
function tinyConfiguration(configuration) {
    if (typeof tinymce === 'undefined') {
        setTimeout(function() {
            tinyConfiguration(configuration);
        }, 100);
        return;
    }

    if (!configuration) {
        configuration = {};
    }

    if (typeof configuration.editor_selector != 'undefined') {
        configuration.selector = '.' + configuration.editor_selector;
    }

    var tinymce_config = {
        /* MAIN SETTINGS */
        deprecation_warnings: true,
        selector: 'rte',
        cache_suffix: '?mod=psy_localizer',
        theme: 'silver',
        //skin: 'oxide',
        branding: false,
        language: localizer_editor_iso,
        // pagebreak
        plugins: 'template autoresize print preview importcss searchreplace autolink autosave directionality code visualblocks visualchars fullscreen image imagetools link media template codesample table charmap hr nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons tabfocus '+extra_plugins,
        toolbar: 'fullscreen code | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | ltr rtl | ' +
            'outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak hr | ' +
            'image media link anchor abbr | table charmap emoticons | codesample edit_attributes template | ' +
            'fontselect fontsizeselect formatselect ' + extra_plugins_toolbar,
        mobile: {
            theme: 'mobile',
            menubar: true,
            plugins: 'autosave lists autolink image',
            toolbar1: 'styleselect fontsizeselect fontselect forecolor backcolor',
            toolbar2: 'bold italic image undo',
            style_formats: [
                { title: 'Headings', items: [
                        { title: 'Heading 1', format: 'h1' },
                        { title: 'Heading 2', format: 'h2' },
                        { title: 'Heading 3', format: 'h3' },
                        { title: 'Heading 4', format: 'h4' },
                        { title: 'Heading 5', format: 'h5' },
                        { title: 'Heading 6', format: 'h6' }
                    ]},
                { title: 'Inline', items: [
                        { title: 'Bold', format: 'bold' },
                        { title: 'Italic', format: 'italic' },
                        { title: 'Underline', format: 'underline' },
                        { title: 'Strikethrough', format: 'strikethrough' },
                        { title: 'Superscript', format: 'superscript' },
                        { title: 'Subscript', format: 'subscript' },
                        { title: 'Code', format: 'code' }
                    ]},
                { title: 'Blocks', items: [
                        { title: 'Paragraph', format: 'p' },
                        { title: 'Blockquote', format: 'blockquote' },
                        { title: 'Div', format: 'div' },
                        { title: 'Pre', format: 'pre' }
                    ]},
                { title: 'Align', items: [
                        { title: 'Left', format: 'alignleft' },
                        { title: 'Center', format: 'aligncenter' },
                        { title: 'Right', format: 'alignright' },
                        { title: 'Justify', format: 'alignjustify' }
                    ]}
            ]
        },
        toolbar_mode: 'sliding',
        menubar: 'file edit view insert format tools table help',
        quickbars_insert_toolbar: 'quickimage numlist bullist | codesample hr ',
        quickbars_selection_toolbar: 'bold quicklink | forecolor backcolor  | h2 h3 blockquote fontsizeselect',
        //quickbars_image_toolbar: 'alignleft aligncenter alignright | rotateleft rotateright | imageoptions',
        contextmenu: 'undo redo | abbr anchor emoticons edit_attributes | help',
        external_plugins: {
            "filemanager":  localizer_moduleUrl + "views/libs/filemanager/plugin.min.js",
        },

        /* Filemanager plugin start */
        filemanager_title:"مدیریت فایل ها" ,
        filemanager_crossdomain: true,
        external_filemanager_path: localizer_moduleUrl + "views/libs/filemanager/",
        /* Filemanager plugin end */
        /**/

        content_css: [
            //localizer_editor_skin_tinymce
            localizer_moduleUrl + 'views/css/admin/localizer-editor-skin.css'
            //localizer_moduleUrl + 'views/css/custom.css',
            //localizer_moduleUrl + 'views/libs/bootstrap/css/bootstrap.min.css',
        ],

        allow_conditional_comments: true,
        allow_html_in_named_anchor: true,
        allow_unsafe_link_target: true,
        entity_encoding: 'raw',
        valid_elements: '*[*]',
        valid_children: "+body[meta|style|script|iframe|section|link],+pre[iframe|section|script|div|p|br|span|img|style|h1|h2|h3|h4|h5|link],+div[meta|video|source],+video[source],*[*],+label[embed|sub|sup|textarea|strong|strike|small|em|form|frame|iframe|input|select|legend|button|div|img|h1|h2|h3|h4|h5|h6|h7|span|p|section|pre|b|u|i|a|ol|ul|li|table|td|tr|th|tbody|thead]",
        extended_valid_elements: 'script[src|async|defer|type|charset]',
        forced_root_block : true,//#test - false#
        indentation : '30px',
        browser_spellcheck: true,
        file_picker_types: 'file image media',
        directionality: localizer_directionality,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        document_base_url: localizer_base_url,
        end_container_on_empty_block: true,
        suffix: '.min',

        /* CONFIGURATION */
        fontsize_formats: "4px 5px 6px 7px 8px 9px 10px 11px 12px 13px 14px 15px 16px 17px 18px 19px 20px 21px 22px 23px 24px 25px 26px 27px 28px 29px 30px 31px 32px 33px 34px 35px 36px 40px 48px 56px 62px 72px 86px 92px 110px 130px 150px 180px",
        menu: {
            file: { title: 'File', items: 'newdocument restoredraft | preview | print ' },
            edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
            view: { title: 'View', items: 'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen' },
            insert: { title: 'Insert', items: 'image link media template codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime' },
            format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript codeformat | formats blockformats fontformats fontsizes align lineheight | forecolor backcolor | removeformat' },
            tools: { title: 'Tools', items: 'code wordcount edit_attributes' },
            table: { title: 'Table', items: 'inserttable | cell row column | tableprops deletetable' },
            help: { title: 'Help', items: 'help' }
        },

        /* ESSENTIAL PLUGIN SETTINGS */

        /* autolink */
        default_link_target: '',
        link_default_protocol: 'https',
        link_assume_external_targets: true,
        link_context_toolbar: true,
        link_title: true,
        link_quicklink: true,
        rel_list: [
            {title: 'None', value: ''},
            {title: 'No Referrer', value: 'noreferrer'},
            {title: 'External Link', value: 'external'},
            {title: 'Alternate', value: 'alternate'},
            {title: 'Author', value: 'author'},
            {title: 'Dns-prefetch', value: 'dns-prefetch'},
            {title: 'Help', value: 'help'},
            {title: 'Icon', value: 'icon'},
            {title: 'License', value: 'license'},
            {title: 'Next', value: 'next'},
            {title: 'Pingback', value: 'pingback'},
            {title: 'Preconnect', value: 'preconnect'},
            {title: 'Prefetch', value: 'prefetch'},
            {title: 'Preload', value: 'preload'},
            {title: 'Prerender', value: 'prerender'},
            {title: 'Prev', value: 'prev'},
            {title: 'Search', value: 'search'},
            {title: 'Stylesheet', value: 'stylesheet'}
        ],
        target_list: [
            {title: 'None', value: ''},
            {title: 'Same page', value: '_self'},
            {title: 'New page', value: '_blank'},
            {title: 'Parent frame', value: '_parent'},
            {title: 'Top', value: '_top'}
        ],
        link_class_list: [
            {title: 'None', value: ''},
            {title: 'Lightbox', value: 'supertiny_link_lightbox'},
        ],

        /* list */
        lists_indent_on_tab: true,

        /* media */
        media_alt_source: true,
        media_dimensions: true,
        media_filter_html: true,
        media_live_embeds: true,
        media_poster: true,

        /* nonbreaking */
        nonbreaking_force_tab: false,
        nonbreaking_wrap: true,

        /* noneditable */
        noneditable_editable_class: 'mceEditable',
        noneditable_noneditable_class: 'mceNonEditable',

        /* pagebreak */
        pagebreak_split_block: false,
        pagebreak_separator: '<!-- Page break -->',

        /* paste */
        paste_block_drop: true,
        paste_data_images: true,
        paste_as_text: false,
        paste_enable_default_filters: true,
        paste_filter_drop: true,
        paste_tab_spaces: 4,
        paste_merge_formats: true,
        paste_webkit_styles: 'color font-size',
        paste_remove_styles_if_webkit: true,
        smart_paste: true,

        /* autosave */
        autosave_ask_before_unload: true,
        autosave_interval: '20s',
        autosave_prefix: 'tinymce-autosave-{path}{query}-{id}-',
        autosave_restore_when_empty: true,
        autosave_retention: '60m',

        /*colorpicker */
        custom_colors: true,
        visual: true,
        color_cols: 5,
        color_map: [
            '#BFEDD2', 'Light Green',
            '#FBEEB8', 'Light Yellow',
            '#F8CAC6', 'Light Red',
            '#ECCAFA', 'Light Purple',
            '#C2E0F4', 'Light Blue',

            '#7cd320', 'Green',
            '#fbe329', 'Yellow',
            '#d0121a', 'Red',
            '#8f28fe', 'Purple',
            '#2c9ffd', 'Blue',

            '#2DC26B', 'Green',
            '#F1C40F', 'Yellow',
            '#E03E2D', 'Red',
            '#B96AD9', 'Purple',
            '#3598DB', 'Blue',

            '#169179', 'Dark Turquoise',
            '#E67E23', 'Orange',
            '#BA372A', 'Dark Red',
            '#843FA1', 'Dark Purple',
            '#236FA1', 'Dark Blue',

            '#ECF0F1', 'Light Gray',
            '#CED4D9', 'Medium Gray',
            '#95A5A6', 'Gray',
            '#7E8C8D', 'Dark Gray',
            '#34495E', 'Navy Blue',

            '#000000', 'Black',
            '#ffffff', 'White'
        ],

        /* emojis */
        emoticons_append: {
            custom_mind_explode: {
                keywords: ['radioactive', 'risk', 'chemical'],
                category: 'Miscellaneous',
                char: ('\u2622'),
            }
        },
        emoticons_database: 'emojis',
        //emoticons_database_url: '/emojis.min.js',

        /* image */
        a11y_advanced_options: true,
        image_advtab: true,
        image_title: true,
        block_unsupported_drop: true,
        images_reuse_filename: false,

        /* insertdatetime */
        insertdatetime_formats: ['%H:%M:%S','%H:%M','%I:%M:%S %p','%I:%M %p', '%Y-%m-%d', '%Y/%m/%d', '%d-%m-%Y', '%d/%m/%Y', '%D', '%m/%d/%Y', '%d %B, %Y', '%a %d %B, %Y', '%A', '%a', '%B', '%b', '%d %A', '%d %a', '%a %d', '%A %d'],
        insertdatetime_element: true,

        /* table */
        table_appearance_options: true,
        table_grid: true,
        table_tab_navigation: true,
        table_sizing_mode: 'auto',
        table_advtab: true,
        table_cell_advtab: true,
        table_row_advtab: true,
        table_resize_bars: true,
        table_style_by_css: false,

        /* table of content */
        toc_depth: 3,
        toc_header: 'h2',
        toc_class: 'mce-toc',

        /* visualblocks and visualchars*/
        visualblocks_default_state: false,
        visualchars_default_state: false,

        /* EXTRA PLUGINS SETTINGS */

        /* Codemirror plugin start */
        codemirror: {
            indentOnInit: true, // Whether or not to indent code on init.
            fullscreen: false,   // Default setting is false
            path: localizer_moduleUrl + 'views/libs/codemirror', // Path to CodeMirror distribution
            config: {           // CodeMirror config object
                mode: 'htmlmixed',
                lineNumbers: true
            },
            width: 800,         // Default value is 800
            height: 600,        // Default value is 550
            saveCursorPosition: true,    // Insert caret marker
            jsFiles: [          // Additional JS files to load
                'mode/clike/clike.js',
                'mode/php/php.js',
                'mode/css/css.js',
                'mode/htmlmixed/htmlmixed.js',
                'mode/javascript/javascript.js'
            ],
            cssFiles: [
                'theme/neat.css',
                'theme/elegant.css'
            ]
        },
        /* Codemirror plugin end */

        /* codesample */
        codesample_global_prismjs: true,
        codesample_languages: [
            {text: 'HTML/XML', value: 'markup'},
            {text: 'JavaScript', value: 'javascript'},
            {text: 'JSON', value: 'json'},
            {text: 'CSS', value: 'css'},
            {text: 'PHP', value: 'php'},
            {text: 'Nginx', value: 'nginx'},
            {text: 'SQL', value: 'sql'},
            {text: 'HTTP', value: 'http'},
        ],

        /* Remove CDATA in scripts */
        init_instance_callback : function(editor) {
            // jw: this code is heavily borrowed from tinymce.jquery.js:12231 but modified so that it will
            //     just remove the escaping and not add it back.
            editor.serializer.addNodeFilter('script,style', function(nodes, name) {
                var i = nodes.length, node, value, type;

                function trim(value) {
                    /*jshint maxlen:255 */
                    /*eslint max-len:0 */
                    return value.replace(/(<!--\[CDATA\[|\]\]-->)/g, '\n')
                        .replace(/^[\r\n]*|[\r\n]*$/g, '')
                        .replace(/^\s*((<!--)?(\s*\/\/)?\s*<!\[CDATA\[|(<!--\s*)?\/\*\s*<!\[CDATA\[\s*\*\/|(\/\/)?\s*<!--|\/\*\s*<!--\s*\*\/)\s*[\r\n]*/gi, '')
                        .replace(/\s*(\/\*\s*\]\]>\s*\*\/(-->)?|\s*\/\/\s*\]\]>(-->)?|\/\/\s*(-->)?|\]\]>|\/\*\s*-->\s*\*\/|\s*-->\s*)\s*$/g, '');
                }
                while (i--) {
                    node = nodes[i];
                    value = node.firstChild ? node.firstChild.value : '';

                    if (value.length > 0) {
                        node.firstChild.value = trim(value);
                    }
                }
            });
        },
    };

    $.each(tinymce_config, function (index, el) {
        if (configuration[index] === undefined)
            configuration[index] = el;
    });
    setTimeout(function() {
        //tinymce.overrideDefaults(configuration);
        tinymce.init(configuration);
    }, 10);
}
function displayTinyMCE(selector = null) {
    // Selectors
    if (selector === null) {
        selector = 'localizer_tiny5';
        $('textarea').each(function(index, textarea){
            if ($('textarea[id="'+textarea.id+'"]').hasClass('autoload_rte') &&
                !$('textarea[id="'+textarea.id+'"]').hasClass(selector)) {
                $('textarea[id="'+textarea.id+'"]').removeClass('autoload_rte').addClass(selector);
            }
        });
    }

    // Display editor
    tinyConfiguration({
        editor_selector : selector,
        setup : function(ed) {
            ed.on('LoadContent', function(ed) {
                // Fix modal windows visual issues with elements.
                $('.tox-tinymce-aux').attr('style', 'z-index: 9999 !important;position: relative;');

                handleCounterTiny(tinymce.activeEditor.id);
            });
            ed.on('change', function(ed, e) {
                tinymce.triggerSave();
                handleCounterTiny(tinymce.activeEditor.id);
            });
            ed.on('blur', function(ed) {
                tinymce.triggerSave();
            });
            // Default values
            ed.on('init', function (e)
            {
                tinymce.activeEditor.getDoc().body.style.fontSize = default_font_size;
                tinymce.activeEditor.getDoc().body.style.fontFamily = default_font;
                handleCounterTiny(tinymce.activeEditor.id);
            });
        }
    });
}
function handleCounterTiny(id) {
    let textarea = $('#'+id);
    let counter = textarea.attr('counter');
    let counter_type = textarea.attr('counter_type');
    let max = tinyMCE.activeEditor.getContent({format : 'text'}).length;

    textarea.parent().find('span.currentLength').text(max);
    if ('recommended' !== counter_type && max > counter) {
        textarea.parent().find('span.currentLength').addClass('text-danger').css('font-weight', 'bold');
        // Display alert
        alert(word_limit);
    } else {
        textarea.parent().find('span.currentLength').removeClass('text-danger').css('font-weight', 'inherit');
    }
}