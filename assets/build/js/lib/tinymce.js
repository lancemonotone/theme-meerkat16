(function ( $ ) {
  'use strict';
  tinymce.PluginManager.add( 'm16_custom', function ( editor, url ) {

    editor.addButton( 'm16_formatselect', function () {
      var items = [];
      var blocks = [
        ['Paragraph', 'p'],
        ['Page Subheader', 'h2'],
        ['Section Header', 'h3'],
        ['Section Subheader', 'h4'],
        ['Title', 'h5'],
        ['Subtitle', 'h6'],
        ['Preformatted', 'pre']
      ];

      tinymce.each( blocks, function ( block ) {
        items.push( {
          text: block[0],
          value: block[1],
        } );
      } );

      return {
        type: 'listbox',
        text: blocks[0][0],
        values: items,
        fixedWidth: true,
        onselect: toggleFormat,
        onPostRender: createListBoxChangeHandler( items )
      };
    } );

    function toggleFormat(fmt) {
      if (fmt.control) {
        fmt = fmt.control.value();
      }

      if (fmt) {
        editor.execCommand('mceToggleFormat', false, fmt);
      }
    }

    function createListBoxChangeHandler( items, formatName ) {
      return function () {
        var self = this;

        editor.on( 'nodeChange', function ( e ) {
          var formatter = editor.formatter;
          var value = null;

          tinymce.each( e.parents, function ( node ) {
            tinymce.each( items, function ( item ) {
              if ( formatName ) {
                if ( formatter.matchNode( node, formatName, { value: item.value } ) ) {
                  value = item.value;
                }
              } else {
                if ( formatter.matchNode( node, item.value ) ) {
                  value = item.value;
                }
              }

              if ( value ) {
                return false;
              }
            } );

            if ( value ) {
              return false;
            }
          } );

          self.value( value );
        } );
      };
    }

  } ); // end tinymce.PluginManager.add

})( jQuery );
