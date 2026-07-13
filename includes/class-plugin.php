<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_document_type' ) );
		add_action( 'add_meta_boxes_agris_document', array( $this, 'add_document_meta_box' ) );
		add_action( 'save_post_agris_document', array( $this, 'save_document_meta' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
		add_action( 'wp_ajax_agris_contact', array( $this, 'handle_contact' ) );
		add_action( 'wp_ajax_nopriv_agris_contact', array( $this, 'handle_contact' ) );
		add_action( 'admin_notices', array( $this, 'dependency_notice' ) );

		if ( is_admin() ) {
			require_once AGRIS_WIDGETS_PATH . 'includes/class-template-applier.php';
			require_once AGRIS_WIDGETS_PATH . 'includes/class-github-updater.php';
			new Template_Applier();
			new Github_Updater();
		}

		if ( did_action( 'elementor/loaded' ) ) {
			$this->boot_elementor();
		} else {
			add_action( 'elementor/loaded', array( $this, 'boot_elementor' ) );
		}
	}

	public static function activate(): void {
		self::instance()->register_document_type();
		flush_rewrite_rules();
	}

	public function boot_elementor(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'comuna-agris',
			array(
				'title' => esc_html__( 'Comuna Agriș', 'comuna-agris' ),
				'icon'  => 'eicon-site-identity',
			)
		);
	}

	public function register_assets(): void {
		wp_register_style( 'agris-widgets', AGRIS_WIDGETS_URL . 'assets/css/frontend.css', array(), AGRIS_WIDGETS_VERSION );
		wp_register_script( 'agris-widgets', AGRIS_WIDGETS_URL . 'assets/js/frontend.js', array(), AGRIS_WIDGETS_VERSION, true );
		wp_localize_script(
			'agris-widgets',
			'agrisWidgets',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'agris-contact' ),
				'i18n'    => array(
					'error'   => esc_html__( 'A apărut o eroare. Încercați din nou.', 'comuna-agris' ),
					'sending' => esc_html__( 'Se trimite…', 'comuna-agris' ),
				),
			)
		);
	}

	public function enqueue_editor_styles(): void {
		wp_enqueue_style( 'agris-editor', AGRIS_WIDGETS_URL . 'assets/css/editor.css', array(), AGRIS_WIDGETS_VERSION );
	}

	public function register_widgets( $widgets_manager ): void {
		require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-base.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-global-widgets.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-content-widgets.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-collection-widgets.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-template-widgets.php';

		$classes = array(
			Widgets\Site_Header::class,
			Widgets\Site_Footer::class,
			Widgets\Accessibility_Tools::class,
			Widgets\Home_Hero::class,
			Widgets\Page_Hero::class,
			Widgets\Section_Heading::class,
			Widgets\Services_Grid::class,
			Widgets\Content_Media::class,
			Widgets\Person_Profile::class,
			Widgets\Schedule_Grid::class,
			Widgets\Council_Members::class,
			Widgets\Link_List::class,
			Widgets\Contact_Details::class,
			Widgets\Contact_Form::class,
			Widgets\Cta_Banner::class,
			Widgets\Photo_Gallery::class,
			Widgets\Data_Table::class,
			Widgets\Stats_Bars::class,
			Widgets\News_Grid::class,
			Widgets\Document_Grid::class,
			Widgets\Document_Library::class,
			Widgets\Post_Archive::class,
			Widgets\Single_Post::class,
			Widgets\Search_Box::class,
		);

		foreach ( $classes as $class ) {
			$widgets_manager->register( new $class() );
		}
	}

	public function register_document_type(): void {
		register_post_type(
			'agris_document',
			array(
				'labels' => array(
					'name'          => __( 'Documente', 'comuna-agris' ),
					'singular_name' => __( 'Document', 'comuna-agris' ),
					'add_new_item'  => __( 'Adaugă document', 'comuna-agris' ),
					'edit_item'     => __( 'Editează documentul', 'comuna-agris' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-media-document',
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'documente' ),
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);

		register_taxonomy(
			'agris_document_category',
			'agris_document',
			array(
				'labels'            => array(
					'name'          => __( 'Categorii documente', 'comuna-agris' ),
					'singular_name' => __( 'Categorie document', 'comuna-agris' ),
				),
				'public'            => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'categorie-document' ),
			)
		);
    }

	public function add_document_meta_box(): void {
		add_meta_box(
			'agris-document-file',
			__( 'Fișierul documentului', 'comuna-agris' ),
			array( $this, 'render_document_meta_box' ),
			'agris_document',
			'normal',
			'high'
		);
	}

	public function render_document_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'agris_document_meta', 'agris_document_nonce' );
		$file = get_post_meta( $post->ID, 'agris_file_url', true );
		$icon = get_post_meta( $post->ID, 'agris_document_icon', true ) ?: 'PDF';
		?>
		<p><label for="agris-file-url"><strong><?php esc_html_e( 'URL fișier (PDF, DOCX etc.)', 'comuna-agris' ); ?></strong></label></p>
		<input class="widefat" id="agris-file-url" name="agris_file_url" type="url" value="<?php echo esc_attr( $file ); ?>" placeholder="https://…/document.pdf">
		<p class="description"><?php esc_html_e( 'Încărcați fișierul în Biblioteca Media și copiați aici URL-ul. Dacă rămâne gol, cardul deschide pagina documentului.', 'comuna-agris' ); ?></p>
		<p><label for="agris-document-icon"><strong><?php esc_html_e( 'Marcaj scurt', 'comuna-agris' ); ?></strong></label></p>
		<input id="agris-document-icon" name="agris_document_icon" type="text" maxlength="6" value="<?php echo esc_attr( $icon ); ?>" placeholder="PDF">
		<p class="description"><?php esc_html_e( 'Exemple: PDF, HCL, AN, PV, FIN.', 'comuna-agris' ); ?></p>
		<?php
	}

	public function save_document_meta( int $post_id ): void {
		if ( ! isset( $_POST['agris_document_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['agris_document_nonce'] ) ), 'agris_document_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'agris_file_url', esc_url_raw( wp_unslash( $_POST['agris_file_url'] ?? '' ) ) );
		update_post_meta( $post_id, 'agris_document_icon', strtoupper( sanitize_text_field( wp_unslash( $_POST['agris_document_icon'] ?? 'PDF' ) ) ) );
	}

	public function handle_contact(): void {
		check_ajax_referer( 'agris-contact', 'nonce' );
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => __( 'Mesaj trimis.', 'comuna-agris' ) ) );
		}

		$ip_key = 'agris_contact_' . md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
		if ( get_transient( $ip_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Așteptați un minut înainte de a trimite din nou.', 'comuna-agris' ) ), 429 );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		$to      = sanitize_email( wp_unslash( $_POST['recipient'] ?? '' ) );
		$to_hash = sanitize_text_field( wp_unslash( $_POST['recipient_hash'] ?? '' ) );

		if ( ! $name || ! is_email( $email ) || ! $subject || ! $message || ! is_email( $to ) || ! hash_equals( wp_hash( $to . '|agris_contact' ), $to_hash ) ) {
			wp_send_json_error( array( 'message' => __( 'Completați corect toate câmpurile obligatorii.', 'comuna-agris' ) ), 422 );
		}

		$body = sprintf( "Nume: %s\nEmail: %s\n\n%s", $name, $email, $message );
		$sent = wp_mail( $to, '[Comuna Agriș] ' . $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => __( 'Mesajul nu a putut fi trimis. Verificați configurarea emailului.', 'comuna-agris' ) ), 500 );
		}

		set_transient( $ip_key, 1, MINUTE_IN_SECONDS );
		wp_send_json_success( array( 'message' => __( 'Mesajul a fost trimis. Vă mulțumim!', 'comuna-agris' ) ) );
	}

	public function dependency_notice(): void {
		if ( current_user_can( 'activate_plugins' ) && ! did_action( 'elementor/loaded' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Comuna Agriș Elementor Widgets necesită pluginul Elementor activ.', 'comuna-agris' ) . '</p></div>';
		}
	}
}
