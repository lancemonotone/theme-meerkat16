jQuery(document).ready(function($){
    //Show Home Info on 'More' link click
    $('.more-info .more-info-title').live('click',function(e){
        $(this).siblings('.home-info').toggle();
        $(this).html(($(this).text().search('More') >= 0) ? '&laquo;&nbsp;Less' : 'More&nbsp;&raquo;');
    });

    //Hide Home Info on 'Hide/Less' link click
    $('.home-info .less-info-title').live('click',function(e){
        $(this).parent('.home-info').hide();
        $(this).parent().parent().children('.more-info-title').show();
    });
});

/************************************************************************
* A-Z functions
*************************************************************************/
// IIFE - Immediately Invoked Function Expression
// http://gregfranko.com/jquery-best-practices/#/7
(function($, window, document){
    // Cache elements
    var $input = $('#a-z-filter input');
    var $results = $('a-z-list');
    // Exit if these elements are not present.
    if(!$input.length){
        return;
    }

    // The $ is now locally scoped
    // Listen for the jQuery ready event on the document
    $(function(){

        // params: change, keydown(e), clearfilter
        if($input.length){
            $input.setup_filter(change, keydown, clear);
        }

        $('.a-z-alpha a').click(function(e){
            e.preventDefault();
            // Reset filter so as not to confuse people.
            $input.val($input.data('placeholder'));
            // hide message if it exists
            $('#a-z-message').hide();
            // Process letter clicked.
            var letter = $(this).html();
            //console.log('give me a letter: ' + letter);
            $('.a-z-section').each(function(i){
                if (letter == 'all'){
                    $(this).parent().show();
                    $(this).siblings().show();
                }
                else if (letter == '#' && $(this).hasClass('a-z-0-9')){
                    $(this).parent().show();
                    $(this).siblings().show();
                }
                else if ($(this).hasClass('a-z-' + letter)){
                    $(this).parent().show();
                    $(this).siblings().show();
                }
                else {
                    $(this).parent().hide();
                }
            });
        });

        $results.prepend('<div id="a-z-message" style="display:none"><p>No match found. Please try another combination.</p></div>');
    });

    function change(){
        clear();
        var filter = $input.val();
        if (filter){
            // finds all links in a list that contain the input
            // and hides the ones not containing the input
            $('.a-z-item').find("a:not(:Contains(" + filter + "))").parent().hide();
            $('.a-z-item').find("a:Contains(" + filter + ")").parent().show();

            var found = 0;
            $('.letter-group').each(function(i){
                c = 0;
                $('.a-z-item', this).each(function(j){
                    if ($(this).css('display') != 'none'){
                        c++;
                        $(this).show();

                    }
                });
                if (c == 0){
                    $(this).hide();
                } else {
                    found++;
                }
            });
            if (!found){
                // show a message saying 'no results'
                $('#a-z-message').show();
            }
            return false;
        }else{
            $('#a-z-message').hide();
        }
    }

    function keydown(e){
        // prevent hitting return from submitting form, which takes us to front page :<
        if(e.keyCode == 13){
            e.preventDefault();
            return false;
        }
    }

    function clear(){
        $('.letter-group, .a-z-item, .a-z-section').show();
    }

}(window.jQuery, window, document));
// The global jQuery object is passed as a parameter

/************************************************************************
* end A-Z functions
*************************************************************************/

/************************************************************************
* Directory functions
*************************************************************************/
// IIFE - Immediately Invoked Function Expression
// http://gregfranko.com/jquery-best-practices/#/7
(function($, window, document){
    var $input = $('#searchform_directory_name');
    var $select = $('#searchform_directory_department');
    var $results = $("#results_directory");
    
    // Exit if these elements are not present.
    if(!$input.length){
        return;
    }
    
    // The $ is now locally scoped
    // Listen for the jQuery ready event on the document
    $(function(){
        // Set up filter on .filter elements.
        if($input.length){
            //params: change, keydown, clearfilter
            $input.setup_filter(get_directory_search, keydown, get_directory_search);
        }
        // Load results when department is changed.
        $select.change(function(){
            $input.blur();
            do_search();
        });
        // Perform search on page load.
        //do_search();
    });
    
    function keydown(e){
        //Get last keystroke
        var code = (e.keyCode ? e.keyCode : e.which);
        //Space, period, apostrophe, hypen --> don't search.
        if (code == 32 || code == 190 || code == 222 || code == 109){
            return
        }
        get_directory_search();
    }
    
    function get_directory_search(){
        // Queue a search.
        if($input.val().length > 1 || $select.val().length > 1){
            window.setTimeout(do_search, 700, true);
        } else {
            $results.html('');
        }
    }

    // The rest of the code goes here!
    // Retrieve records from PeopleSoft.
    function do_search(){
        var self = get_directory_search;
        // cache variables
        var directory_name = $input.val();
        var directory_dept = $select.val();
        var directory_name_field = $input.attr('name');
        var directory_dept_field = $select.attr('name');

        // Don't search for no reason.
        if ((directory_name == $input.data('placeholder') || !directory_name.length) && !directory_dept.length){
            $results.html('');
            return;
        }

        // No need to search the same terms again.
        if (directory_name == self.last_search && directory_dept == self.last_department){
            return;
        } else {
            // set name to "" if it's == to filtertext.
            if(directory_name != $input.data('placeholder')){
                self.last_search = directory_name;
            } else {
                self.last_search = '';
            }
            self.last_department = directory_dept;
        }

        var serialized = directory_name_field + '=' + self.last_search + '&' + directory_dept_field + '=' + self.last_department;
        var data = { action: 'load_directory',  'data' : serialized };
        //alert(serialize);

        //$.get(wmsAjax.ajaxurl, data, function(response){
        $.post(wmsdir_ajaxurl, data, function(response){
            $results.html(response);
            add_more_info_toggles(); //Add the 'More'/'Less' toggle links to each record
            add_dept_links();
        });
    }
    
    function add_dept_links(){
        var $this_url = $.url().attr('directory');
        $('.record-info-index')
            .find('.dept').each(function(){
                $(this).wrapInner('<a style="display:block;" href="' + $this_url + '?department=' + $(this).data('dept_id') + '"></a>');
            });
            
        $('.record-info-index').find('.dept a').click(function(e){
            e.preventDefault();
            $select.val($(this).parent().data('dept_id')).change();
        });
    }

    //Adds links to the DOM for 'More'/'Less' that toggle home information visibility
    //Needs to be accessible globally, for other scripts in this directory.
    function add_more_info_toggles(){
        $("div.wms-directory-wrapper div.vcard").mouseover(function(){
            $(this).addClass("wrapper-hover");
        }).mouseout(function(){
            $(this).removeClass("wrapper-hover");
        });

        //Add 'More' link for when Home Info is hidden
        $('.more-info').prepend('<div class="more-info-title" rel="toggle">More &raquo;</div>');
        //Hide Home Info automatically on load
        $('.home-info').hide();
    }
}(window.jQuery, window, document));
// The global jQuery object is passed as a parameter
/************************************************************************
* end Directory functions
*************************************************************************/
