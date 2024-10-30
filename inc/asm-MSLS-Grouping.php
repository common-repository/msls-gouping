<?php
/*
Plugin Core: MSLS Grouping
Plugin URI: http://asumaru.com/business/wp-plugins/asm-msls-grouping/
Description: You can multilingualize by 'Multisite Language Switcher' with grouping the sites of the plural same languages.
Author: Asumaru Corp.
Version: 0.2.1
Author URI: http://asumaru.com/
Created: 2017.01.01
Updated: 2017.03.01
Updated: 2017.03.28

*/

/**
 * Core class.
 *
 * @since 0.1
**/
class asm_MSLS_Grouping_Class{

	/**
	 * Any functions.
	 *
	 * @since 0.1
	 * @var array
	 * @access private
	 */
	private $funcs = array();

	/**
	 * Variable caching.
	 *
	 * @since 0.1
	 * @var array
	 * @access private
	 */
	private $cache = array();

	/**
	 * Variable defaults.
	 *
	 * @since 0.1
	 * @var array
	 * @access public
	 */
	public $defaults = array(
		'atts'	=> array(
			'name'	=> '',
			'show'	=> true,
		),
		'site_options' => array(
			'allow_duplicate_languages'	=> array( false, 'checkbox', "Allow Duplicate Languages", "You allow for the same language to repeat in 'Multisite Language Switcher'." ),
		),
		'sc_args'	=> array(
			'raw'			=> false,

			'item_class'	=> 'SameLangSite-%id%',
			'lang_class'	=> 'LanguageSite-%lang%',
			'group_class'	=> 'SiteGroup-%group%',
			'before_item'	=> "\t",
			'after_item'	=> "\n",
			'link_target'	=> 'site_%id%',

			'before_output'	=> '<ul class="SameLangSites">
',
			'after_output'	=> '</ul>
',
		),
		'sc_content'	=> '%before_item%<li class="%item_class% %lang_class% %group_class%"><a href="%url%" target="%link_target%">%name%</a></li>%after_item%
',
	);

	/**
	 * Default strings for no groupkey.
	 *
	 * @since 0.1
	 * @var string
	 * @access public
	 */
	public $noGroup = '-- no group --';

	/**
	 * Default locale and language.
	 *
	 * @since 0.1
	 * @var array
	 * @access public
	 */
	public $emptyLang = array( 'us', 'en_US' );

	/**
	 * Default boolean to allow duplicate languages.
	 *
	 * @since 0.1
	 * @var bool
	 * @access public
	 */
	public $allow_duplicate_languages = false;

	/**
	 * Constractor for PHP 4
	 *
	 * @since 0.1
	 * @access public
	 */
	public function asm_MSLS_Grouping_Class(){
		add_action( 'init', array( &$this, 'init'), 20);

		if( is_admin() ){
			add_action( 'admin_init', array( &$this, 'admin_init' ), 20 );
		}

		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ), 20);

		add_action( 'widgets_init', create_function('', 'return register_widget("asm_SameLangSites_Widget");') );

		if( apply_filters( 'asm_language-flugs.admin-bar', true ) ){
			add_action( 'admin_bar_menu', array( &$this, 'admin_bar_menu' ), 20, 20);
		}

	}

	/**
	 * Constractor
	 *
	 * @since 0.1
	 * @access public
	 */
	public function __construct(){
		return $this->asm_MSLS_Grouping_Class();
	}

	/**
	 * Call method in this class.
	 *
	 * @since 0.1
	 * @param string $name Calling function name.
	 * @param mix $args Calling function arguments.
	 * @return mix function result.
	 * @access public
	 */
	public function __call( $name, $args){
		if( method_exists( $this, $name)){
			return call_user_func_array( array( &$this, $name), $args);
		}
		if( is_array( $this->funcs) && array_key_exists( $name, $this->funcs)){
			if( is_scalar($this->funcs[$name])){
				$func = create_function( '', $this->funcs[$name]);
			}
			else
			{
				$func = $this->funcs[$name];
			}
			return call_user_func_array( $func, $args);
		}
		return null;
	}

	/**
	 * Action Hook. WP Initialize.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function init(){
		if( class_exists( 'MslsOptions' ) ){
			$this->cache['MslsOptions'] = new MslsOptions();
		}

		add_filter( 'msls_blog_collection_construct', array( &$this, 'msls_blog_collection_construct' ), 20, 20 );

		add_shortcode( 'SameLangSites', array( &$this, 'get_SameLangSites' ) );
	}

	/**
	 * Action Hook. WP-Admin Initialize.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function admin_init(){
		global $asm_textdomain;
		if( function_exists( 'add_settings_field' ) ){
			add_settings_field( 'group_key', __( 'Group Key', $asm_textdomain ), array( $this, 'group_key' ), 'MslsAdmin', 'advanced_section' );
		}
		add_action( 'update_wpmu_options', array( &$this, 'update_wpmu_options' ), 20 );
		add_action( 'wpmu_options', array( &$this, 'wpmu_options' ), 20 );
		add_filter( 'plugin_locale', array( &$this, 'plugin_locale' ), 20, 10 );
		if( apply_filters( 'asm_language-column.blogs.network', true ) ){
			add_filter( 'wpmu_blogs_columns', array( &$this, 'wpmu_blogs_columns' ), 20, 20 );
			add_action( 'manage_sites_custom_column', array( &$this, 'manage_sites_custom_column' ), 20, 20 );
		}
		if( apply_filters( 'asm_force_WPLANG.options_general', true ) ){
			add_action( 'load-options-general.php', array( &$this, 'load_options_general' ), 20, 20 );
		}
		add_action( 'admin_print_footer_scripts', array( &$this, 'admin_print_footer_scripts' ), 20, 20 );
	}

	/**
	 * Action Hook. Text domain.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function load_plugin_textdomain(){
		global $asm_textdomain;
		$dir = dirname( __FILE__);
		$domain_name = basename(__FILE__);
		$domain_name = preg_replace( '#\.[a-zA-Z0-9\~]+$#', '', $domain_name );
		$domain_name = strtolower( $domain_name );
		$locale_name  = get_locale();
		$mofile_name  = dirname(__FILE__);
		$mofile_name  = preg_replace( '#/inc$#', '/languages', $mofile_name );
		$mofile_name .= "/$domain_name-$locale_name.mo";
		load_textdomain( $asm_textdomain, $mofile_name);
	}

	/**
	 * Filter Hook. Plugin locale.
	 *
	 * @since 0.1
	 * @param string $locale original locale.
	 * @param string $domain text domain.
	 * @return string locale.
	 * @access public
	 */
	public function plugin_locale( $locale, $domain = null ){
		global $asm_textdomain;
		$res = $locale;
		return $res;
	}

	/**
	 * Action Hook. Update site option.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function update_wpmu_options(){
		foreach( (array) $this->defaults['site_options'] as $akey => $aval ){
			if( $_POST[$akey] === null || $_POST[$akey] === false ){
				$value = null;
			}
			else
			{
				$value = wp_unslash( $_POST[$akey] );
			}
			update_site_option( $akey, $value );
		}
	}

	/**
	 * Action Hook. Add in site option form.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function wpmu_options(){
		global $asm_textdomain;
		foreach( (array) $this->defaults['site_options'] as $akey => $aval ){
			switch( $aval[1] ){
				case 'checkbox':
?>
		<table class="form-table msls_group">
			<tr>
				<th scope="row"><?php _e( $aval[2], $asm_textdomain ); ?></th>
				<td>
					<label>
						<input name="<?php echo $akey; ?>" type="checkbox" id="<?php echo $akey; ?>" value="yes"<?php checked( get_site_option( $akey ), "yes" ); ?> />
						<?php _e( $aval[3], $asm_textdomain ) ?>
					</label>
				</td>
			</tr>
		</table>
<?php
					break;
			}
		}
	}

	/**
	 * Setting field 'group key'.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function group_key(){
		global $asm_textdomain;
		echo $this->render_input( 'group_key', '40' );
		echo '<br/><span class="attention"> ';
		_e( '(*) If there is some blogs with the same language, you define "Group Key" to same blog-set.', $asm_textdomain );
		echo ' </span>';
	}

	/**
	 * Render form-element (checkbox)
	 *
	 * @since 0.1
	 * @param string $key MslsOptions property name.
	 * @return string checkbox tag.
	 * @access public
	 */
	public function render_checkbox( $key ) {
		$template = '<input type="checkbox" id="%1$s" name="msls[%1$s]" value="1" %2$s/>';
		if( ! is_scalar( $key ) ) return '';
		if( ! is_object( $this->cache['MslsOptions'] )
		|| ! is_callable( array( $this->cache['MslsOptions'] , 'instance' ) ) ){
			return sprintf(
				$template,
				$key,
				checked( 1, 0, false )
			);
		}
		$MslsOptions = MslsOptions::instance();
		$val = isset( $MslsOptions->$key ) ? $MslsOptions->$key : 0;
//		if( ! isset( $MslsOptions->$key ) ) return '';
		return sprintf(
			$template,
			$key,
			checked( 1, $val, false )
		);
	}

	/**
	 * Render form-element (text-input)
	 *
	 * @since 0.1
	 * @param string $key MslsOptions property.
	 * @param init $size limit text size.
	 * @return string text-input tag.
	 * @access public
	 */
	public function render_input( $key, $size = '30' ) {
		$template = '<input id="%1$s" name="msls[%1$s]" value="%2$s" size="%3$s"/>';
		if( ! is_scalar( $key ) || empty( $key ) ) return '';
		if( ! is_object( $this->cache['MslsOptions'] )
		|| ! is_callable( array( $this->cache['MslsOptions'] , 'instance' ) ) ){
			return sprintf(
				$template,
				$key,
				'',
				$size
			);
		}
		$MslsOptions = MslsOptions::instance();
		$val = isset( $MslsOptions->$key ) ? $MslsOptions->$key : '';
//		if( ! isset( $MslsOptions->$key ) ) return '';
		return sprintf(
			$template,
			$key,
			esc_attr( $val ),
			$size
		);
	}

	/**
	 * Render form-element (select)
	 *
	 * @since 0.1
	 * @param string $key MslsOptions property.
	 * @param array $arr option values and descriptions.
	 * @return string select and option tag.
	 * @access public
	 */
	public function render_select( $key, array $arr, $selected = '' ) {
		$tmpl_opts = '<option value="%s" %s>%s</option>';
		$tmpl_sels = '<select id="%1$s" name="msls[%1$s]">%2$s</select>';
		if( ! is_scalar( $key ) || empty( $key ) ) return '';
		$options = array();

		$MslsOptions = MslsOptions::instance();
		$selected = isset( $MslsOptions->$key ) ? $MslsOptions->$key : '';
//		if( ! isset( $MslsOptions->$key ) ) return '';

		foreach ( $arr as $value => $description ) {
			$options[] = sprintf(
				$tmpl_opts,
				$value,
				selected( $value, $selected, false ),
				$description
			);
		}

		if( ! is_object( $this->cache['MslsOptions'] )
		|| ! is_callable( array( $this->cache['MslsOptions'] , 'instance' ) ) ){
			return sprintf(
				$tmpl_sels,
				$key,
				implode( '', $options )
			);
		}
		return sprintf(
			$tmpl_sels,
			$key,
			implode( '', $options )
		);
	}

	/**
	 * Filter Hook. MSLS Collection to group blog-objects.
	 *
	 * @since 0.1
	 * @param array $blogs_collection Collection of blog-objects.
	 * @return array Collection  to group blog-objects.
	 * @access public
	 */
	public function msls_blog_collection_construct( $blogs_collection = array() ){
		$res = $blogs_collection;
		if( function_exists( 'get_current_blog_id' ) ){
			$blog_id = get_current_blog_id();
		}
		$blog_id = empty( $blog_id ) ? 1 : $blog_id;

		$allow_duplicate_languages = $this->defaults['site_options']['allow_duplicate_languages'][0];
		if( function_exists( 'get_site_option' ) ){
			$allow_duplicate_languages = get_site_option( 'allow_duplicate_languages', $allow_duplicate_languages );
		}

		$options = new stdClass;
		if( class_exists( 'MslsOptions' ) && is_callable( array( 'MslsOptions', 'instance' ) ) ){
			$options = MslsOptions::instance();
		}
		$group_key = @ $options->group_key;
		$group_key = empty( $group_key ) ? $this->noGroup : $group_key;

		$lang = get_option( 'WPLANG' );
		$lang = empty( $lang ) ? $this->emptyLang[0] : $lang;
		$langs = array();

		$this->cache['current_blog'] = array(
			'blog_id'	=> $blog_id,
			'group_key'	=> $group_key,
			'group_str'	=> preg_replace( '/[^a-zA-Z01-9_\-]+/', '_', $group_key ),
			'language'	=> $lang,
		);

		foreach( (array) $blogs_collection as $akey => $aval ){
			$aBlog_id = @ $aval->userblog_id;
			$aOptions = get_blog_option( $aBlog_id, 'msls' );
			$aGroup_key = empty( $aOptions['group_key'] ) ? $this->noGroup : $aOptions['group_key'];
			$aLang = get_blog_option( $aBlog_id, 'WPLANG' );
			$aLang = empty( $aLang ) ? $this->emptyLang[0] : $aLang;

			if( empty( $this->cache['blogs'] ) ) $this->cache['blogs'] = array();
			if( empty( $this->cache['blogs'][$aLang] ) ) $this->cache['blogs'][$aLang] = array();
			if( empty( $this->cache['blogs'][$aLang][$aGroup_key] ) ) $this->cache['blogs'][$aLang][$aGroup_key] = $aBlog_id;

			if( $aGroup_key != $group_key ){
				unset( $res[$akey] );
				continue;
			}
			if( ! $allow_duplicate_languages && ! empty( $langs[$aLang] ) ){
				unset( $res[$akey] );
				continue;
			}
			$langs[$aLang] = $aBlog_id;
		}
		return $res;
	}

	/**
	 * Get Same language Sites.
	 *
	 * @since 0.1
	 * @param mix $atts Option Array.
	 * @param string $template HTML tag Template.
	 * @return string HTML Tags or Array.
	 * @access public
	 */
	public function get_SameLangSites( $atts = '', $template = ''){
		$res = null;

		$args = wp_parse_args( $atts, $this->defaults['sc_args'] );
		$args = apply_filters( 'asm_SameLangSites_args', $args );
		$template = trim( $template );
		if( empty( $template ) ){
			$template = $this->defaults['sc_content'];
		}

		$current_blog = $this->cache['current_blog'];
		$blog_id = $current_blog['blog_id'];
		$lang = $current_blog['language'];
		$lang = empty( $lang ) ? $this->emptyLang[0] : $lang;
		$group_key = $current_blog['group_key'];
		$grpstr = $current_blog['group_str'];
		$blogs = $this->cache['blogs'];
		$siblingBlogs = (array) $blogs[$lang];
		$Siblings = array();

		$status = array(
			'public',
			'archived',
			'mature',
			'spam',
			'deleted',
		);

		foreach( $siblingBlogs as $akey => $aval ){
			if( $aval == $blog_id ) continue;
			$Siblings[$aval] = array();
			switch_to_blog( $aval );
			$Siblings[$aval]['name']	= get_bloginfo('name');
			$Siblings[$aval]['url']	= get_bloginfo('siteurl');
			restore_current_blog();
			foreach( $status as $aStatus ){
				$Siblings[$aval][$aStatus] = get_blog_status( $aval, $aStatus );
			}
		}
		$res = $Siblings = apply_filters( 'asm_SameLangSites_array', (array) $Siblings );

		$mslsOptions = null;
		if( class_exists( 'MslsOptions' ) && is_callable( 'MslsOptions::instance' ) ){
			$mslsOptions = MslsOptions::instance();
			if( is_object( $mslsOptions ) && is_callable( array( $mslsOptions, 'get_flag_url' ) ) ){
				$flag_url = $mslsOptions->get_flag_url( $lang );
			}
		}

		$args['raw'] = is_scalar( $args['raw'] ) ? strtolower( trim( $args['raw'] ) ) : (bool) $args['raw'];
		if( empty( $args['raw'] ) || $args['raw'] == 'no' || $args['raw'] == 'off' ){
			$li = '';
			foreach( (array) $Siblings as $akey => $aval ){
				$aArgs = (array) $args;
				$aArgs['id']	= $akey;
				$aArgs['url']	= $aval['url'];
				$aArgs['name']	= $aval['name'];
				$aArgs = apply_filters( 'asm_SameLangSites_li_args', $aArgs, $akey, $aval );
				$ali = $template;
				foreach( $aArgs as $bkey => $bval ){
					$bval = str_replace( "%id%", $akey, $bval );
					$bval = str_replace( "%lang%", $lang, $bval );
					$bval = str_replace( "%flag_url%", $flag_url, $bval );
					$bval = str_replace( "%group%", $grpstr, $bval );
					$ali = str_replace( "%{$bkey}%", $bval, $ali );
				}
				$li .= apply_filters( 'asm_SameLangSites_li', $ali, $aArgs );
			}

			$res = apply_filters( 'asm_SameLangSites_ul',
					$args['before_output'] . $li . $args['after_output'],
					$args,
					$Siblings
			);
			$res = preg_replace( '/[\n\r]+/', "\n", $res );

		}

		return $res;
	}

	/**
	 * Filter Hook. Blogs Columns to add 'Language' column in sites table.
	 *
	 * @since 0.2
	 * @param array $sites_columns Columns in WP_MS_Sites_List_Table.
	 * @return array columns.
	 * @access public
	 */
	public function wpmu_blogs_columns( $sites_columns = array() ){
		global $asm_textdomain;
		$res = array();
		$arr = (array) $sites_columns;
		$addkey = 'language';
		$addName = __( "Language", $asm_textdomain ) . ' (' . __( "Group Key", $asm_textdomain ) . ')';
		reset( $arr );
		if( empty( $arr[ $addkey ] ) ){
			foreach( $arr as $akey => $aval ){
				$res[$akey] = $aval;
				if( $akey == 'blogname' ){
					$res[ $addkey ] = $addName;
					if( empty( $this->cache['addCSS'] ) ) $this->cache['addCSS'] = array();
					$this->cache['addCSS'][$addkey] = <<<EOS
/* Language Column in Sites List */
table.sites	th.column-language ,
table.sites	td.column-language {
	width : 15%;
}
table.sites	.column-language .attantion {
	color : red;
EOS;
				}
			}
		}

		return $res;
	}

	/**
	 * Action Hook. Admin print fotter scripts.
	 *
	 * @since 0.2
	 * @access public
	 */
	public function admin_print_footer_scripts(){
		if( ! empty( $this->cache['addScripts'] ) && is_array( $this->cache['addScripts'] ) ){
			$scripts = '';
			foreach( $this->cache['addScripts'] as $akey => $aval ){
				$scripts .= $aval . "\n";
			}
			$scripts = trim( $scripts );
			$style = <<<EOS
<script type="text/javascript">
{$scripts}
</script>

EOS;
			echo $style;
		}
		if( ! empty( $this->cache['addCSS'] ) && is_array( $this->cache['addCSS'] ) ){
			$css = '';
			foreach( $this->cache['addCSS'] as $akey => $aval ){
				$css .= $aval . "\n";
			}
			$css = trim( $css );
			$style = <<<EOS
<style type="text/css">
{$css}
</style>

EOS;
			echo $style;
		}
	}

	/**
	 * Get current blog language.
	 *
	 * @since 0.2
	 * @param init $blog_id Blog ID.
	 * @return array blog locale adn group key, language name, language flag url.
	 * @access public
	 */
	public function get_current_blog_language( $blog_id = 0 ){
		global $asm_textdomain;

		$blog_id = (int) $blog_id;
		if ( empty( $blog_id ) )
			$blog_id = get_current_blog_id();
		if ( get_current_blog_id() == $blog_id ){
			$locale = get_option('WPLANG');
			$msls = get_option('msls');
		}
		else
		{
			switch_to_blog( $blog_id );
			$locale = get_option('WPLANG');
			$msls = get_option('msls');
			restore_current_blog();
		}

		$locale = empty( $locale ) ? $this->emptyLang[1] : $locale;
		$msls = empty( $msls ) ? array() : (array) $msls;
		$group_key = $msls['group_key'];
		$group_key = $group_key == $this->noGroup ? '' : $group_key;
		$langName = empty( $msls['description'] ) ? $locale : $msls['description'];
		$flag_url = '';
		if( class_exists( 'MslsOptions' ) && is_callable( 'MslsOptions::instance' ) ){
			$mslsOptions = MslsOptions::instance();
			if( is_callable( array( $mslsOptions, 'get_flag_url' ) ) ){
				$flag_url = MslsOptions::instance()->get_flag_url( $locale );
			}
		}
		$options = compact( 'locale', 'group_key', 'langName', 'flag_url', 'msls' );
		return apply_filters(
			"asm_get_current_blog_language",
			$options,
			$blog_id
		);
	}

	/**
	 * Action Hook. 'Language' column on manage_sites_custom_column.
	 *
	 * @since 0.2
	 * @param string $column_name current column name.
	 * @param string $blog_id Blog ID.
	 * @access public
	 */
	public function manage_sites_custom_column( $column_name = '', $blog_id = 0 ){
		global $asm_textdomain;
		$Colmuns = array( 'language' );
		if( ! in_array( $column_name, $Colmuns ) ){
			return;
		}
		if( $column_name == 'language' ){
			$options = $this->get_current_blog_language( $blog_id );
			extract( $options );

			$flagImg = empty( $flag_url ) ? '' : <<<EOS
<img alt="{$locale}" src="{$flag_url}" class="language-flag">
EOS;
			$grpSpan = empty( $group_key ) ? '' : <<<EOS
<span class="language-group">{$group_key}</span>
EOS;
			$grpSpan = empty( $grpSpan ) ? '' : <<<EOS

<p class="blog-groupkey">({$grpSpan})</p>

EOS;
			$noMsls = ! empty( $msls ) ? '' : __( "'Multisite Language Switcher' is unestablished.", $asm_textdomain );
			$noMsls = empty( $noMsls ) ? '' : <<<EOS
<p class="attantion">{$noMsls}</p>

EOS;
			$tags = <<<EOS
<p class="blog-Languages">{$flagImg}<span class="language-name">{$langName}</span></p>{$grpSpan}{$noMsls}

EOS;
			$columnTitle = ucfirst( $column_name );
			echo apply_filters(
				"asm_{$columnTitle}Column",
				$tags,
				$column_name,
				$blog_id,
				$options
			);
		}

	}

	/**
	 * Action Hook. Language Flags on admin_bar_menu.
	 *
	 * Add CSS and Javascripts to show language flugs on Admin-Bar.
	 *
	 * @since 0.2
	 * @param object $wp_admin_bar WP Admin-bar.
	 * @access public
	 */
	public function admin_bar_menu( $wp_admin_bar ){
		global $asm_textdomain;
		$options = $this->get_current_blog_language();
		extract( $options );

		$grpSpan = empty( $group_key ) ? '' : <<<EOS
" [{$group_key}]"
EOS;

		if( empty( $this->cache['addCSS'] ) ) $this->cache['addCSS'] = array();

		$flags = array();
		foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
			$aOptions = $this->get_current_blog_language( $blog->userblog_id );
			$aBlogID = "blog-{$blog->userblog_id}";
			$linkID = "#wp-admin-bar-{$aBlogID}";
			$flags[$aOptions['locale']] = $aOptions['flag_url'];
			$gkClass = empty( $aOptions['group_key'] ) ? '' : urlencode( $aOptions['group_key'] );

			$this->cache['addScripts'][$aBlogID] = <<<EOS
	jQuery("{$linkID}").addClass("blog-language-{$aOptions['locale']}");
	if( "{$gkClass}" != "" ) jQuery("{$linkID}").addClass("blog-group-{$gkClass}");

EOS;
		}
		foreach ( $flags as $akey => $aval ) {
			$this->cache['addCSS']['lang-' . $akey] = <<<EOS
#wpadminbar .quicklinks li.blog-language-{$akey} .blavatar::before {
	content: url({$aval});
}
EOS;
		}

		if( ! is_network_admin() ){
			$this->cache['addCSS']['site-name'] = <<<EOS
.wp-admin #wpadminbar #wp-admin-bar-site-name > .ab-item::before ,
#wpadminbar #wp-admin-bar-site-name > .ab-item::before {
	content: url({$flag_url}){$grpSpan};
	margin: 0.25em 0.25em 0.1em 0;
	font-size: 1em;
}
EOS;
		}
	}

	/**
	 * Action Hook. Deficient cancellation on Options General.
	 *
	 * @since 0.2
	 * @param object $wp_admin_bar WP Admin-bar.
	 * @access public
	 */
	public function load_options_general(){
		$lang = get_option( 'WPLANG' );
		$this->cache['addScripts']['options_general'] = <<<EOS
	if( jQuery("select#WPLANG option:selected").val() == "{$lang}" ){
	}
	else
	if(jQuery("select#WPLANG option").is("[data-installed][value='{$lang}']")){
		jQuery("select#WPLANG option:selected").removeAttr("selected");
		jQuery("select#WPLANG option[data-installed][value='{$lang}']").attr("selected","selected");
	}
EOS;
	}

}

if( class_exists('asm_MSLS_Grouping_Class') && !is_object( $GLOBALS['asm_MSLS_Grouping_Class'])){
	$GLOBALS['asm_MSLS_Grouping_Class'] = new asm_MSLS_Grouping_Class();
}

/**
 * Same Language Sites Widgets Class.
 *
 * @since 0.1
 * @see WP_Widget
**/
class asm_SameLangSites_Widget extends WP_Widget {

	/**
	 * Form defaults.
	 *
	 * @since 0.1
	 * @var array
	 * @access private
	 */
	private $form_defs = array(
			'title' => '',
	);

	/**
	 * Sets up a new Same Language Sites widget instance.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function __construct() {
		global $asm_textdomain;
		$widget_ops = array(
			'classname' => 'widget_SameLangSites',
			'description' => __( "Same Language Sites List", $asm_textdomain),
			'customize_selective_refresh' => true,
		);
		parent::__construct('SameLangSites', __('Same Language Sites',$asm_textdomain), $widget_ops);
		$this->alt_option_name = 'widget_SameLangSites';

	}

	/**
	 * Outputs the content for the current Same Language Sites widget instance.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Same Language Sites  widget instance.
	 */
	public function widget( $args, $instance ) {
		global $asm_MSLS_Grouping_Class;

		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? __( 'Same Language Sites', $asm_textdomain ) : $instance['title'],
			$instance,
			$this->id_base
		);

		$template = apply_filters(
			'asm_SameLangSites_widget_template',
			$instance['template'],
			$instance,
			$this->id_base
		);

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo apply_filters( 'asm_SameLangSites_widget', do_shortcode( "\n[SameLangSites]{$template}[/SameLangSites]" ), $args, $instance );
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Same Language Sites widget instance.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            asm_SameLangSites_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, $this->form_defs );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['template'] = $new_instance['template'];
		} else {
			$instance['template'] = wp_kses_post( $new_instance['template'] );
		}

		return $instance;
	}

	/**
	 * Outputs the settings form for the Same Language Sites widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		global $asm_MSLS_Grouping_Class;

		$instance = wp_parse_args( (array) $instance, $this->form_defs );
		$title = sanitize_text_field( $instance['title'] );
		$template = esc_textarea( $instance['template'] );
		$template = empty( $template ) ? $asm_MSLS_Grouping_Class->defaults['sc_content'] : $template;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:' ); ?></label>
		<textarea class="widefat" rows="10" cols="20" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>"><?php echo $template; ?></textarea></p>
		<?php
	}

}

?>