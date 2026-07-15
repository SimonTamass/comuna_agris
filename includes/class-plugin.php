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
		require_once AGRIS_WIDGETS_PATH . 'includes/class-assets.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/class-legacy-content.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/class-widget-registry.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/class-elementor.php';
		require_once AGRIS_WIDGETS_PATH . 'includes/class-frontend-templates.php';

		add_action( 'init', array( $this, 'register_document_type' ) );
		add_action( 'add_meta_boxes_agris_document', array( $this, 'add_document_meta_box' ) );
		add_action( 'save_post_agris_document', array( $this, 'save_document_meta' ) );
		add_action( 'wp_ajax_agris_contact', array( $this, 'handle_contact' ) );
		add_action( 'wp_ajax_nopriv_agris_contact', array( $this, 'handle_contact' ) );
		add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_contact_fallback_assets' ), 40 );
		add_filter( 'the_content', array( $this, 'append_contact_fallback' ), 30 );
		add_filter( 'the_title', array( $this, 'clean_display_title' ), 20, 2 );
		Assets::instance();
		Frontend_Templates::instance();

		if ( is_admin() ) {
			require_once AGRIS_WIDGETS_PATH . 'includes/class-template-applier.php';
			new Template_Applier();
		}

		if ( did_action( 'elementor/loaded' ) ) {
			Elementor_Integration::instance();
		} else {
			add_action( 'elementor/loaded', array( Elementor_Integration::class, 'instance' ) );
		}
	}

	public static function activate(): void {
		self::instance()->register_document_type();
		flush_rewrite_rules();
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

	private function is_contact_page( ?\WP_Post $post = null ): bool {
		$post = $post ?: get_post();
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
			return false;
		}

		$identity = strtolower( remove_accents( $post->post_name . ' ' . $post->post_title ) );
		return (bool) preg_match( '/\b(contact|elerhetosegeink|elerhetoseg|elerhet)\b/', $identity );
	}

	private function contact_language( ?\WP_Post $post = null ): string {
		$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			return $language;
		}

		$post = $post ?: get_post();
		$path = $post instanceof \WP_Post ? (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH ) : '';
		return str_contains( $path, '/hu/' ) ? 'hu' : 'ro';
	}

	public function enqueue_contact_fallback_assets(): void {
		if ( is_singular( 'page' ) && $this->is_contact_page( get_queried_object() instanceof \WP_Post ? get_queried_object() : null ) ) {
			wp_enqueue_style( 'agris-widgets' );
			wp_enqueue_script( 'agris-widgets' );
		}
	}

	public function clean_display_title( string $title, int $post_id = 0 ): string {
		if ( is_admin() || '' === $title || ! $post_id ) {
			return $title;
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
			return $title;
		}

		if ( preg_match( '/-(ro|hu)$/i', (string) $post->post_name ) && preg_match( '/^(.*?)\s+(ro|hu)$/i', trim( wp_strip_all_tags( $title ) ), $matches ) ) {
			return rtrim( $matches[1] );
		}

		return $title;
	}

	public function append_contact_fallback( string $content ): string {
		if ( is_admin() || ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $this->is_contact_page( $post ) || str_contains( $content, 'agris-contact-form' ) || str_contains( $content, 'agris-contact-fallback' ) ) {
			return $content;
		}

		return $content . $this->render_contact_fallback( $this->contact_language( $post ) );
	}

	private function render_contact_fallback( string $language ): string {
		$is_hungarian = 'hu' === $language;
		$recipient = 'primaria@comunaagris.ro';
		$copy = $is_hungarian ? array(
			'kicker' => 'Elérhetőség',
			'title' => 'Egri Község Polgármesteri Hivatala',
			'description' => 'Hivatali elérhetőségek és ügyfélfogadási információk.',
			'address_code' => 'CÍM',
			'address' => 'Románia, 447066, Egri, Csűry Bálint utca 68., Szatmár megye',
			'hours_code' => 'IDŐ',
			'hours' => 'Hétfő-péntek: 8:00-16:00',
			'map_title' => 'Térkép',
			'form_kicker' => 'Üzenet',
			'form_title' => 'Írjon nekünk',
			'form_description' => 'Az űrlap kötelező mezőinek kitöltése után üzenete közvetlenül a hivatalhoz érkezik.',
			'name' => 'Név',
			'email' => 'Email',
			'subject' => 'Tárgy',
			'message' => 'Üzenet',
			'button' => 'Üzenet küldése',
			'privacy' => 'Az adatokat kizárólag a megkeresés megválaszolására használjuk.',
		) : array(
			'kicker' => 'Contact',
			'title' => 'Primăria Comunei Agriș',
			'description' => 'Date de contact și program de lucru pentru cetățeni.',
			'address_code' => 'LOC',
			'address' => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare',
			'hours_code' => 'ORĂ',
			'hours' => 'Luni-Vineri: 8:00-16:00',
			'map_title' => 'Hartă',
			'form_kicker' => 'Mesaj',
			'form_title' => 'Trimiteți-ne un mesaj',
			'form_description' => 'Completați câmpurile obligatorii pentru a trimite mesajul direct către primărie.',
			'name' => 'Nume și prenume',
			'email' => 'Email',
			'subject' => 'Subiect',
			'message' => 'Mesaj',
			'button' => 'Trimite mesajul',
			'privacy' => 'Datele sunt folosite exclusiv pentru a răspunde solicitării.',
		);

		ob_start();
		?>
		<section class="agris-contact-fallback agris-shell">
			<div class="agris-contact-layout">
				<div class="agris-contact-card">
					<div class="agris-kicker"><?php echo esc_html( $copy['kicker'] ); ?></div>
					<h2 class="agris-title"><?php echo esc_html( $copy['title'] ); ?></h2>
					<p class="agris-lead"><?php echo esc_html( $copy['description'] ); ?></p>
					<div class="agris-detail-list">
						<div><b><?php echo esc_html( $copy['address_code'] ); ?></b><span><?php echo esc_html( $copy['address'] ); ?></span></div>
						<a href="tel:0261878112"><b>TEL</b><span>0261 878 112</span></a>
						<div><b>FAX</b><span>0261 878 111</span></div>
						<a href="mailto:<?php echo esc_attr( antispambot( $recipient ) ); ?>"><b>@</b><span><?php echo esc_html( antispambot( $recipient ) ); ?></span></a>
						<div><b><?php echo esc_html( $copy['hours_code'] ); ?></b><span><?php echo esc_html( $copy['hours'] ); ?></span></div>
					</div>
				</div>
				<iframe class="agris-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=47.8816707,23.0048293&amp;z=15&amp;output=embed" title="<?php echo esc_attr( $copy['map_title'] ); ?>"></iframe>
			</div>
			<section class="agris-form-card">
				<div class="agris-kicker"><?php echo esc_html( $copy['form_kicker'] ); ?></div>
				<h2 class="agris-title"><?php echo esc_html( $copy['form_title'] ); ?></h2>
				<p class="agris-lead"><?php echo esc_html( $copy['form_description'] ); ?></p>
				<form class="agris-contact-form" novalidate>
					<input type="hidden" name="recipient" value="<?php echo esc_attr( $recipient ); ?>">
					<input type="hidden" name="recipient_hash" value="<?php echo esc_attr( wp_hash( $recipient . '|agris_contact' ) ); ?>">
					<input type="hidden" name="language" value="<?php echo esc_attr( $is_hungarian ? 'hu' : 'ro' ); ?>">
					<div class="agris-form-two">
						<label><?php echo esc_html( $copy['name'] ); ?> *<input name="name" required autocomplete="name"></label>
						<label><?php echo esc_html( $copy['email'] ); ?> *<input name="email" type="email" required autocomplete="email"></label>
					</div>
					<label><?php echo esc_html( $copy['subject'] ); ?> *<input name="subject" required></label>
					<label><?php echo esc_html( $copy['message'] ); ?> *<textarea name="message" rows="6" required></textarea></label>
					<label class="agris-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
					<p class="agris-form-privacy"><?php echo esc_html( $copy['privacy'] ); ?></p>
					<button class="agris-button agris-button-primary" type="submit"><?php echo esc_html( $copy['button'] ); ?></button>
					<div class="agris-form-status" role="status" aria-live="polite"></div>
				</form>
			</section>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	public function handle_contact(): void {
		check_ajax_referer( 'agris-contact', 'nonce' );
		$language = sanitize_key( wp_unslash( $_POST['language'] ?? 'ro' ) );
		$is_hungarian = 'hu' === $language;
		$messages = array(
			'sent'    => $is_hungarian ? 'Az üzenetet elküldtük. Köszönjük!' : 'Mesajul a fost trimis. Vă mulțumim!',
			'wait'    => $is_hungarian ? 'Kérjük, várjon egy percet az újabb üzenet elküldése előtt.' : 'Așteptați un minut înainte de a trimite din nou.',
			'invalid' => $is_hungarian ? 'Kérjük, töltse ki helyesen az összes kötelező mezőt.' : 'Completați corect toate câmpurile obligatorii.',
			'failed'  => $is_hungarian ? 'Az üzenetet nem sikerült elküldeni. Kérjük, ellenőrizze az email-beállításokat.' : 'Mesajul nu a putut fi trimis. Verificați configurarea emailului.',
		);
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => $messages['sent'] ) );
		}

		$ip_key = 'agris_contact_' . md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
		if ( get_transient( $ip_key ) ) {
			wp_send_json_error( array( 'message' => $messages['wait'] ), 429 );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		$to      = sanitize_email( wp_unslash( $_POST['recipient'] ?? '' ) );
		$to_hash = sanitize_text_field( wp_unslash( $_POST['recipient_hash'] ?? '' ) );

		if ( ! $name || ! is_email( $email ) || ! $subject || ! $message || ! is_email( $to ) || ! hash_equals( wp_hash( $to . '|agris_contact' ), $to_hash ) ) {
			wp_send_json_error( array( 'message' => $messages['invalid'] ), 422 );
		}

		$body = sprintf( "Nume: %s\nEmail: %s\n\n%s", $name, $email, $message );
		$sent = wp_mail( $to, '[Comuna Agriș] ' . $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => $messages['failed'] ), 500 );
		}

		set_transient( $ip_key, 1, MINUTE_IN_SECONDS );
		wp_send_json_success( array( 'message' => $messages['sent'] ) );
	}

	public function dependency_notice(): void {
		if ( current_user_can( 'activate_plugins' ) && ! did_action( 'elementor/loaded' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Comuna Agriș Elementor Widgets necesită pluginul Elementor activ.', 'comuna-agris' ) . '</p></div>';
		}
	}
}
