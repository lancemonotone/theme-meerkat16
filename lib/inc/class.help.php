 <?php

class wms_help
{
    public $tabs = array(
        // The assoc key represents the ID
        // It is NOT allowed to contain spaces
         'EXAMPLE' => array(
             'title'   => 'TEST ME!'
            ,'content' => 'FOO'
         )
    );

    static public function init()
    {
        $class = __CLASS__ ;
        new $class;
        echo "blajh";
    }

    public function __construct()
    {
        add_action( "load-{$GLOBALS['pagenow']}", array( $this, 'add_tabs' ), 20 );
    }

    public function add_tabs()
    {
        foreach ( $this->tabs as $id => $data )
        {
            get_current_screen()->add_help_tab( array(
                 'id'       => $id
                ,'title'    => __( $data['title'], 'some_textdomain' )
                // Use the content only if you want to add something
                // static on every help tab. Example: Another title inside the tab
                ,'content'  => '<p>Some stuff that stays above every help text</p>'
                ,'callback' => array( $this, 'prepare' )
            ) );
        }
    }

    public function prepare( $screen, $tab )
        {
            printf( 
             '<p>%s</p>'
            ,__( 
                     $tab['callback'][0]->tabs[ $tab['id'] ]['content']
                ,'dmb_textdomain' 
             )
        );
    }
}
add_action( 'load-post.php', array( 'wms_help', 'init' ) );
add_action( 'load-post-new.php', array( 'wms_help', 'init' ) );