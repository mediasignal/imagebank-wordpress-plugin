(function(jQuery) {
	'use strict';

    /**
     * Kuvapankki API methods
     */
    var KuvapankkiAPI = {};
    KuvapankkiAPI.getFields = function(cb) {
        jQuery.post(ajaxurl, { action: 'kuvapankki_fields' }, cb);
    };

    KuvapankkiAPI.getCategories = function(cb) {
        jQuery.post(ajaxurl, { action: 'kuvapankki_categories' }, cb);
    }
    KuvapankkiAPI.getLanguages = function(cb) {
        jQuery.post(ajaxurl, { action: 'kuvapankki_languages' }, cb);
    };

    KuvapankkiAPI.search = function(params, cb) {
        jQuery.post(ajaxurl, { action: 'kuvapankki_search', params: params }, cb);
    };

    /** Properties */
    var Kuvapankki = {};
    Kuvapankki.root = null;
    Kuvapankki.rootToolbar = null;
    Kuvapankki.rootImages = null;
    Kuvapankki.rootSearchBar = null;
    Kuvapankki.rootPaginator = null;

    Kuvapankki.visible = false;
    Kuvapankki.searching = false;
    Kuvapankki.currentLocale = 'fi';
    Kuvapankki.settings = {};

    Kuvapankki.page = 1;
    Kuvapankki.totalPages = 1;
    Kuvapankki.itemsPerPage = 8;
    
    /** 
     * Create modal elements 
     */
    Kuvapankki.create = function() {
        Kuvapankki.root = jQuery('<div class="kuvapankki-modal"></div>');
        Kuvapankki.rootImages = jQuery('<select multiple masonry></select>');

        Kuvapankki.rootToolbar = jQuery('<div class="toolbar"></div>');

        var closeButton = jQuery('<div class="components-button is-default">Peruuta</div>');
        closeButton.click(Kuvapankki.close);

        Kuvapankki.rootToolbar.append(closeButton);

        var importButton = jQuery('<div class="components-button is-default is-primary">Tuo</div>');
        importButton.click(Kuvapankki.save)

        Kuvapankki.rootToolbar.append(importButton);

        var languageSelect = jQuery('<select id="languageselect"></select>');
        KuvapankkiAPI.getLanguages(function(result) {
            result.message.forEach(function(locale, index) {
                var option = jQuery('<option></option>');
                option.val(locale.code);
                option.text(locale.name);

                if (index === 0) {
                    option.attr('selected', true);
                }

                option.appendTo(languageSelect);
            });
        });

        languageSelect.change(function() {
            Kuvapankki.currentLocale = jQuery(this).val();
            Kuvapankki.resetSearch();
        });

        Kuvapankki.rootToolbar.append(languageSelect);

        // Add search elements
        Kuvapankki.rootSearchBar = jQuery('<div class="searchbar"></div>');
        Kuvapankki.rootSearchBar.append('<input id="searchtext" type="text" placeholder="Haku"/>');

        var categorySelect = jQuery('<select id="categoryselect"></select>');
        categorySelect.append('<option val="" selected>Kaikki Kategoriat</option>');

        KuvapankkiAPI.getCategories(function(result) {
            function categoriesToOptions(categories, depth) {
                categories.forEach(function(category) {
                    depth = depth || 0;
    
                    var option = jQuery('<option></option>');
                    option.val(category.id);
    
                    var prefix = " - ".repeat(depth);
                    option.text(prefix + category.name);
    
                    option.appendTo(categorySelect);
    
                    if (category.children) {
                        categoriesToOptions(category.children, depth + 1);
                    }
                });
            }

            categoriesToOptions(result.message.data);
        });

        Kuvapankki.rootSearchBar.append(categorySelect);

        // KuvapankkiAPI.getFields(function(result) { 
        //     console.log("Fields", result);

        //     result.message.forEach(function(field) {
        //         var el = jQuery('<input type="text" class="field-input"/>');
        //         el.attr('placeholder', Kuvapankki.getTranslation(field, 'label', 'fi'));
        //         el.data('id', field.id);

        //         el.appendTo(Kuvapankki.rootSearchBar);
        //     });
        // });

        // Add search button
        var searchbutton = jQuery('<button id="searchbutton" class="components-button is-primary">Etsi</button>');
        searchbutton.appendTo(Kuvapankki.rootSearchBar);
        searchbutton.click(Kuvapankki.resetSearch);

        Kuvapankki.rootPaginator = jQuery('<div class="paginator"></div>');

        // Add elements to modal root
        Kuvapankki.root.append(Kuvapankki.rootToolbar);
        Kuvapankki.root.append(Kuvapankki.rootSearchBar);
        Kuvapankki.root.append(Kuvapankki.rootImages);
        Kuvapankki.root.append(Kuvapankki.rootPaginator);

        // Add modal to body
        jQuery('body').append(Kuvapankki.root);
    };

    /**
     * Display modal
     */
    Kuvapankki.show = function() {
        if (Kuvapankki.visible)
            return;

        if (!Kuvapankki.root) {
            Kuvapankki.create();
        }

        Kuvapankki.search();

        Kuvapankki.root.show();
        Kuvapankki.visible = true;
    };

    /**
     * Page methods
     */
    Kuvapankki.setPage = function(page) {
        Kuvapankki.page = page;
        Kuvapankki.search();
    }

    Kuvapankki.nextPage = function() {
        Kuvapankki.page = Math.min(Kuvapankki.page + 1, Kuvapankki.totalPages);
        Kuvapankki.search();
    };

    Kuvapankki.prevPage = function() {
        Kuvapankki.page = Math.max(1, Kuvapankki.page - 1);
        Kuvapankki.search();
    };

    Kuvapankki.resetSearch = function() {
        Kuvapankki.page = 1;
        Kuvapankki.search();
    };

    Kuvapankki.search = function() {
        if (Kuvapankki.searching) return;

        Kuvapankki.searching = true;

        Kuvapankki.rootImages.empty();

        var filterString = jQuery('#searchtext').val() || "";
        var categories = [];
        var category = parseInt(jQuery('#categoryselect').val());

        if (!isNaN(category)) {
            categories.push(category);
        }

        var params = {
            filterString: filterString,
            products: {
                id: "",
                name: filterString,
                description: filterString
            },
            language: Kuvapankki.currentLocale,
            categories: categories,
            direction: "desc",
            keywords: {},
            orderBy: "created_at",
            page: Kuvapankki.page,
            per_page: Kuvapankki.itemsPerPage,
        };

        console.log(params);
        KuvapankkiAPI.search(params, Kuvapankki.handleSearchResults);
    };

    Kuvapankki.handleSearchResults = function(response) {
        if (!response || response.success !== true) {
            return alert("Tapahtui virhe, tarkista asetukset");
        }

        var products = response.message.data;
        products.forEach(function(product) {
            product.files.forEach(function(file) {
                var option = jQuery('<option></option>');
                option.attr('data-img-src', file.thumbnail_url);
                option.val(file.url);
                option.text(file.name);
                option.data('id', file.id);
                option.data('ext', file.extension);

                Kuvapankki.rootImages.append(option);
            });
        });

        
        Kuvapankki.rootImages.imagepicker({
            hide_select: true,
            show_label: true
        });

        Kuvapankki.updatePaginator(response.message);
        Kuvapankki.searching = false;
    };

    Kuvapankki.getTranslation = function(o, key, locale) 
    {
        locale = locale || Kuvapankki.currentLocale;

        var translation = o.translations.filter(function(t) { return t.locale === locale; });
        if (!translation[0]) {
            return "";
        }

        return translation[0][key];
    };

    Kuvapankki.updatePaginator = function(data) {
        var current_page = data.current_page;
        var last_page = data.last_page;
        var from = data.from;
        var to = data.to;
        var total = data.total;

        Kuvapankki.totalPages = last_page;

        Kuvapankki.rootPaginator.empty();
        
        var prevPage = jQuery('<span class="page">«</span>');
        prevPage.click(Kuvapankki.prevPage);
        prevPage.appendTo(Kuvapankki.rootPaginator);

        /**
         * Display first page, current page and last page
         * UNLESS current page is first or the last, then 2nd value is current-page +/- 1
         */

        var pages = [1];
        
        if (last_page > 1) {
            if (current_page == 1) {
                pages.push(2);
            } else if (current_page === last_page) {
                pages.push(last_page - 1);
            } else {
                pages.push(current_page);
            }
        }

        if (2 < last_page) {
            pages.push(last_page);
        }

        pages.forEach(function(page) {
            var el = jQuery('<span class="page">' + page + '</span>');

            if (page === current_page) {
                el.addClass('current');
            }

            el.click(function() { Kuvapankki.setPage(page); });
            el.appendTo(Kuvapankki.rootPaginator);
        });

        var nextPage = jQuery('<span class="page">»</span>');
        nextPage.click(Kuvapankki.nextPage);
        nextPage.appendTo(Kuvapankki.rootPaginator);
    };

    Kuvapankki.save = function() {
        Kuvapankki.rootImages.data('picker').sync_picker_with_select();
        var values = Kuvapankki.rootImages.val();
        var result = values.map(function(value) {
            var image = Kuvapankki.rootImages.find('option[value="' + value + '"]');

            return {
                link: image.val(),
                name: image.text() + '-' + image.data('id') + '.' + image.data('ext')
            };
        });

        Kuvapankki.settings.success(result);
        Kuvapankki.close();
    };

    Kuvapankki.close = function() {
        if (!Kuvapankki.visible)
            return;

        Kuvapankki.root.hide();
        Kuvapankki.visible = false;
    };

    Kuvapankki.choose = function(params) {
        Kuvapankki.settings = params;
        Kuvapankki.show();
    };

    jQuery( 'body' ).on( 'click', 'a#kuvapankki-file-chooser, button#kuvapankki-file-chooser',  function( e ) {
        e.preventDefault();

        var type = jQuery( this ).data( 'type' );
        var plugin = jQuery( this ).data( 'plugin' );
        var cardinality = jQuery( this ).data( 'cardinality' );
        var extensions = jQuery( this ).data( 'mime-types' );

        Kuvapankki.choose({
          success: function( files ) {
            if ( type == 'url' ) {
              jQuery( '#embed-url-field' ).val( files[0].link ).change();
            }
            else {
              var _count = 0;
              for ( var i = 0; i < files.length; i++ ) {
                if ( cardinality > 1 ) {
                  if ( _count < cardinality ) {
                    external_media_upload( plugin, files[i].link, files[i].name );
                    _count++;
                  }
                }
              }
            }
          },

          extensions: ( type == 'url' ) ? '' : extensions.split( ',' ),
          multiselect: ( type == 'url' ) ? false : true,
          linkType: ( type == 'url' ) ? 'preview' : 'direct'
        });
      });
})(jQuery);
