/*
@codekit-prepend "vendors/list.js"
@codekit-prepend "vendors/list.paging.js"
*/

// Start script
jQuery(document).ready(function($) {


    // Remove area
    $('.blocks-remove-area').click( function() {
        $(this).closest('.blocks-area-row').remove();
        return false;
    });



    // Expand the block
    $('.block-title').live('click', function() {
        $(this).parent().toggleClass('expanded');
    });



    // Remove function
    var removeEventFunction = function() {
        var area = $(this).parents('ul');
        $(this).closest('.block').remove();
        saveFunction(area);
    };



    // Remove block from saved
    $('#normal-sortables .remove-block').click( removeEventFunction );



    // If have blocks-area add draggable and sortable
    if ( $(".blocks-area").closest("body").length > 0 ) {
        $('.list li').draggable( {
            helper:'clone',
            connectToSortable:'.blocks-area',
        });
  
        // Make the areas sortable
        $('.blocks-area').sortable( {
            placeholder: "blocksplaceholder",
                        
            update: function (event, ui) {
                saveFunction($(this));
                $('.remove-block', this).click( removeEventFunction );
            }
        }); 
     }



    // Options for list.js Search on block-title, 8 Items and paging plugin
    var options = {
        valueNames: [ 'block-title' ],
        page: 8,
        plugins: [
            [ 'paging' ]
        ]
    };

    // Fire the list-function
    var blocksList = new List('blocks_save', options);

    // Remove paging if only one page, should be native in lib
    var pages = $(".paging-holder ul").children().length;

    if( pages == 1 ) {
       $('.paging li').remove();
    }

    // Save function
    saveFunction = function(area) {
        var data = [];

        area.find('li').each(function() {
            data.push($(this).attr('data-id'));
        });

        opts = {
            url: ajaxurl,
            type: 'POST',
            async: true,
            cache: false,
            dataType: 'json',
            data:{
                action: 'save_blocks', 
                post_id: $('#post_ID').val(),
                order: data.join(),
                area: area.attr('data-area')
            },

            beforeSend: function() {
                $('.paging-holder').append('<div class="loading"></div>');
            },

            complete: function() { 
                $('.loading').remove();
            },
        };
        $.ajax(opts);
    }



    // Search pages and posts on title
    $('.block-pages-search').bind('keydown keypress keyup change', function() {
        var search = this.value;
        var $li = $('.list-pages li').hide();
        $li.filter(function() {
            return $(this).text().toLowerCase().indexOf(search) >= 0;
        }).show();
    })



    // Toggle Children
    $('.block-parent').live('click', function() {
        $(this).toggleClass('open');
        $(this).closest('.list').find('ul.children').toggleClass('open');
    });


    // Move posts/pages between saved and available  
    $('#blocks-area-control .save-block .delete').live('click', function() {
        var otherSide;

         otherSide = $(this).closest('#blocks-area-control').find('ul.list-pages');

        // Append
        $(this).parent().appendTo(otherSide);
    });

    // Move posts/pages between saved and available  
    $('#blocks-area-control .list-pages .add').live('click', function() {
        var otherSide;

         otherSide = $(this).closest('#blocks-area-control').find('ul.save-block');

        // Append
        $(this).parent().appendTo(otherSide);
    });


    // Save block areas to pages
    $('.areas li').live('click', function(e) {
        e.stopPropagation();
       
        var me = $(this).find('span');

        opts = {
            url: ajaxurl,
            type: 'POST',
            async: true,
            cache: false,
            context: me,
            dataType: 'json',
            data: {
                action: 'save_block_pages'
            },

            beforeSend: function(data) {
                me.addClass('loading');
            },

            complete: function() { 
                me.removeClass('loading');
                if( me.hasClass('saved') ) {
                    me.removeClass('saved');
                } else {
                    me.addClass('saved');
                }
            }
        };
        $.ajax(opts);
    });

    // remove all open classes
    $(document).click( function(){ 
        $(".open").removeClass("open");
    });

    // Open areas-selector upon click
    $('#blocks-area-control .add-areas').click( function(e) {
    
        if( $(this).next().next().hasClass('open') ) {
            $(".open").addClass("open");
        } else {
             $(".open").removeClass("open");
        }

        $(this).next().next().toggleClass('open');

        e.stopPropagation();
    });


    // Simple AJAX-call to flush-cache
    $('.empty-cache').click( function(e) {
        e.preventDefault()

        opts = {
            url: ajaxurl,
            type: 'POST',
            async: true,
            cache: false,
            dataType: 'json',
            data:{
                action: 'empty_cache'
        },

        beforeSend: function() {
            $('.empty-cache-holder').append('<span class="small-loader"></span>');
            return;
        },

        complete: function() { 
            $('.small-loader').remove();
            return;
            },
        };
        $.ajax(opts);
    });



    // Wp-Pointers
    $('a.show-pointer').each(function() {
            
        // vars
        var a = $(this),
        html = a.attr('rel');
        
        
        // create pointer
        a.pointer({
            content: $('#' + html).html(),
            position:{
                my: 'left top', 
                at: 'left bottom'
            },
            close: function() {
                
                a.removeClass('open');
                
            }
        });
        
        
        // click
        a.click(function() {
        
            a.toggleClass('open');
            
        });
        

        // show on hover
        a.hover(function() {
        
            $(this).pointer('open');
            
        }, function() {
            
            if( ! a.hasClass('open') ) {
                $(this).pointer('close');
            }
            
        });
    });
});
