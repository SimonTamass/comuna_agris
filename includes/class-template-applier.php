<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Template_Applier {
	private const NONCE_ACTION = 'agris_apply_template';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_post_agris_apply_template', array( $this, 'handle_apply' ) );
	}

	public function register_admin_page(): void {
		add_management_page(
			__( 'Comuna Agriș rebuild', 'comuna-agris' ),
			__( 'Comuna Agriș rebuild', 'comuna-agris' ),
			'edit_pages',
			'agris-rebuild',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Nu aveți permisiunea necesară.', 'comuna-agris' ) );
		}

		$page_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : $this->find_home_ro_page();
		$post    = $page_id ? get_post( $page_id ) : null;
		$url     = $post ? wp_nonce_url(
			admin_url( 'admin-post.php?action=agris_apply_template&template=home_ro&post_id=' . (int) $post->ID ),
			self::NONCE_ACTION . '_' . (int) $post->ID
		) : '';
		$status  = isset( $_GET['agris_status'] ) ? sanitize_key( wp_unslash( $_GET['agris_status'] ) ) : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Comuna Agriș rebuild', 'comuna-agris' ); ?></h1>
			<?php if ( 'applied' === $status ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Șablonul Elementor pentru pagina de start RO a fost aplicat.', 'comuna-agris' ); ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Acest instrument scrie direct structura Elementor pe pagina existentă, fără să schimbe slugul sau adresa URL.', 'comuna-agris' ); ?></p>
			<table class="widefat striped" style="max-width:820px">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pagină țintă', 'comuna-agris' ); ?></th>
						<td>
							<?php if ( $post ) : ?>
								<strong><?php echo esc_html( get_the_title( $post ) ); ?></strong>
								<br><code><?php echo esc_html( get_permalink( $post ) ); ?></code>
							<?php else : ?>
								<?php esc_html_e( 'Nu am găsit automat pagina /ro/home-ro/. Adăugați parametrul post_id în URL.', 'comuna-agris' ); ?>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Acțiune', 'comuna-agris' ); ?></th>
						<td>
							<?php if ( $post && current_user_can( 'edit_post', $post->ID ) ) : ?>
								<a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Aplică indexul român Elementor', 'comuna-agris' ); ?></a>
							<?php else : ?>
								<button class="button button-primary" disabled><?php esc_html_e( 'Aplică indexul român Elementor', 'comuna-agris' ); ?></button>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function handle_apply(): void {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

		if ( ! $post_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), self::NONCE_ACTION . '_' . $post_id ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'comuna-agris' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Nu aveți permisiunea necesară.', 'comuna-agris' ) );
		}

		$this->apply_home_ro( $post_id );

		wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&post_id=' . $post_id . '&agris_status=applied' ) );
		exit;
	}

	private function apply_home_ro( int $post_id ): void {
		$data = $this->home_ro_data();

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => '',
			)
		);

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.33.0' );
		update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );

		delete_post_meta( $post_id, '_elementor_css' );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	private function find_home_ro_page(): int {
		$page = get_page_by_path( 'home-ro', OBJECT, 'page' );
		if ( $page instanceof \WP_Post ) {
			return (int) $page->ID;
		}

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'title'          => 'Home ro',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $pages ? (int) $pages[0] : 0;
	}

	private function menu_id(): string {
		$menus = wp_get_nav_menus();
		if ( ! $menus ) {
			return '';
		}

		foreach ( $menus as $menu ) {
			$name = strtolower( remove_accents( $menu->name ) );
			if ( str_contains( $name, 'ro' ) || str_contains( $name, 'roman' ) || str_contains( $name, 'principal' ) ) {
				return (string) $menu->term_id;
			}
		}

		return (string) $menus[0]->term_id;
	}

	private function id( string $seed ): string {
		return substr( md5( 'agris-home-ro-' . $seed ), 0, 7 );
	}

	private function link( string $url = '' ): array {
		return array(
			'url'         => $url,
			'is_external' => '',
			'nofollow'    => '',
		);
	}

	private function media(): array {
		return array(
			'id'  => '',
			'url' => '',
		);
	}

	private function repeater( string $seed, array $items ): array {
		foreach ( $items as $index => $item ) {
			$items[ $index ]['_id'] = substr( md5( $seed . '-' . $index ), 0, 7 );
		}

		return $items;
	}

	private function widget( string $seed, string $type, array $settings = array() ): array {
		return array(
			'id'         => $this->id( $seed ),
			'elType'     => 'widget',
			'settings'   => $settings,
			'elements'   => array(),
			'widgetType' => $type,
		);
	}

	private function container( string $seed, array $elements, array $settings = array() ): array {
		return array(
			'id'       => $this->id( $seed ),
			'elType'   => 'container',
			'settings' => array_merge(
				array(
					'content_width' => 'full',
					'flex_direction' => 'column',
					'gap'            => array( 'unit' => 'px', 'size' => 0, 'sizes' => array() ),
					'padding'        => array(
						'unit'     => 'px',
						'top'      => '0',
						'right'    => '0',
						'bottom'   => '0',
						'left'     => '0',
						'isLinked' => true,
					),
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	private function home_ro_data(): array {
		$menu_id = $this->menu_id();

		return array(
			$this->container(
				'header',
				array(
					$this->widget(
						'header-widget',
						'agris-site-header',
						array(
							'official_text'  => 'Site oficial al Primăriei Comunei Agriș, județul Satu Mare, România',
							'trust_text'     => 'Conexiune securizată',
							'mail_url'       => $this->link( 'mailto:primaria@comunaagris.ro' ),
							'logo'           => $this->media(),
							'brand_title'    => 'Comuna Agriș',
							'brand_subtitle' => 'Primăria Comunei Agriș',
							'home_url'       => $this->link( '/ro/home-ro/' ),
							'menu_id'        => $menu_id,
							'cta_text'       => 'Monitorul Oficial',
							'cta_link'       => $this->link( '/ro/monitorul-oficial/' ),
							'sticky'         => 'yes',
							'language_items' => $this->repeater( 'lang', array(
								array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( '/ro/home-ro/' ) ),
								array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( '/hu/home-hu/' ) ),
							) ),
						)
					),
					$this->widget( 'search-modal', 'agris-search-box' ),
				)
			),
			$this->container(
				'hero',
				array(
					$this->widget(
						'hero-widget',
						'agris-home-hero',
						array(
							'eyebrow'        => 'Ghișeul este deschis · Luni-Vineri 8:00-16:00',
							'title'          => 'Servicii publice transparente pentru Comuna Agriș.',
							'description'    => 'Portal modern pentru documente, formulare, hotărâri, anunțuri oficiale și informații utile pentru cetățeni.',
							'primary_text'   => 'Vezi documentele',
							'primary_link'   => $this->link( '/ro/monitorul-oficial/' ),
							'secondary_text' => 'Contact rapid',
							'secondary_link' => $this->link( '/ro/contact/' ),
							'background'     => $this->media(),
							'show_search'    => 'yes',
							'updates_title'  => 'Noutăți din portal',
							'updates_items'  => $this->repeater( 'updates', array(
								array( 'day' => 'AN', 'title' => 'Anunțuri publice', 'meta' => 'Actualizate periodic', 'url' => $this->link( '/ro/anunt/' ) ),
								array( 'day' => 'HCL', 'title' => 'Hotărâri Consiliul Local', 'meta' => 'Arhivă și documente', 'url' => $this->link( '/ro/hotarari/' ) ),
								array( 'day' => 'DOC', 'title' => 'Documente administrative', 'meta' => 'Formulare și registre', 'url' => $this->link( '/ro/informatii-publice/' ) ),
							) ),
						)
					),
				)
			),
			$this->section( 'services-head', 'Servicii', 'Cum vă putem ajuta', 'Acces rapid către cele mai căutate servicii ale primăriei.' ),
			$this->container(
				'services',
				array(
					$this->widget(
						'services-widget',
						'agris-services-grid',
						array(
							'columns'    => '4',
							'items_list' => $this->repeater( 'services', array(
								array( 'icon' => 'TAX', 'title' => 'Taxe și impozite', 'description' => 'Informații pentru plăți, evidențe fiscale și program.', 'url' => $this->link( '/ro/taxe-si-impozite/' ) ),
								array( 'icon' => 'DOC', 'title' => 'Formulare', 'description' => 'Documente tipizate și cereri pentru cetățeni.', 'url' => $this->link( '/ro/formulare/' ) ),
								array( 'icon' => 'URB', 'title' => 'Urbanism', 'description' => 'Certificate, autorizații și informații urbanistice.', 'url' => $this->link( '/ro/urbanism/' ) ),
								array( 'icon' => 'AGR', 'title' => 'Registru agricol', 'description' => 'Servicii și evidențe pentru gospodării și terenuri.', 'url' => $this->link( '/ro/registru-agricol/' ) ),
							) ),
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'news-head', 'Actualități', 'Noutăți și anunțuri', 'Cele mai recente comunicări publicate de Primăria Comunei Agriș.', 'Toate noutățile', '/ro/anunt/' ),
			$this->container(
				'news',
				array(
					$this->widget(
						'news-widget',
						'agris-news-grid',
						array(
							'post_type'    => 'post',
							'category'     => '',
							'count'        => 3,
							'columns'      => '3',
							'orderby'      => 'date',
							'show_excerpt' => 'yes',
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'docs-head', 'Transparență', 'Documente publice', 'Hotărâri, anunțuri și documente administrative într-o bibliotecă filtrabilă.' ),
			$this->container(
				'documents',
				array(
					$this->widget(
						'documents-widget',
						'agris-document-library',
						array(
							'kicker'       => 'Documente',
							'title'        => 'Bibliotecă publică',
							'description'  => 'Căutați rapid documentele publicate de instituție.',
							'count'        => 9,
							'columns'      => '3',
							'show_filters' => 'yes',
							'show_search'  => 'yes',
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'about',
				array(
					$this->widget(
						'about-widget',
						'agris-content-media',
						array(
							'kicker'     => 'Comuna',
							'title'      => 'Agriș, comunitate cu administrație aproape de cetățeni',
							'description'=> 'Informații locale, servicii publice și comunicare instituțională într-un singur portal.',
							'content'    => '<p>Comuna Agriș își publică online informațiile importante pentru ca locuitorii să găsească rapid documentele, anunțurile și datele de contact necesare.</p><p>Noua structură Elementor permite actualizarea fiecărei secțiuni separat, fără schimbarea adreselor existente ale paginilor.</p>',
							'image'      => $this->media(),
							'image_side' => 'right',
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'cta',
				array(
					$this->widget(
						'cta-widget',
						'agris-cta-banner',
						array(
							'kicker'      => 'Participare publică',
							'title'       => 'Aveți nevoie de informații sau documente?',
							'description' => 'Contactați primăria sau consultați Monitorul Oficial local pentru cele mai noi documente.',
							'image'       => $this->media(),
							'button_text' => 'Contact',
							'button_link' => $this->link( '/ro/contact/' ),
						)
					),
				)
			),
			$this->container(
				'footer',
				array(
					$this->widget(
						'footer-widget',
						'agris-site-footer',
						array(
							'title'       => 'Comuna Agriș',
							'subtitle'    => 'Primăria Comunei Agriș',
							'description' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.',
							'links'       => $this->repeater( 'footer-links', array(
								array( 'column' => 'Primăria', 'label' => 'Conducere', 'url' => $this->link( '/ro/primaria/' ) ),
								array( 'column' => 'Primăria', 'label' => 'Consiliul Local', 'url' => $this->link( '/ro/consiliul-local/' ) ),
								array( 'column' => 'Informații publice', 'label' => 'Anunțuri', 'url' => $this->link( '/ro/anunt/' ) ),
								array( 'column' => 'Informații publice', 'label' => 'Monitorul Oficial', 'url' => $this->link( '/ro/monitorul-oficial/' ) ),
							) ),
							'phone'       => '0261 878 112',
							'email'       => 'primaria@comunaagris.ro',
							'address'     => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare',
							'copyright'   => 'Toate drepturile rezervate Comuna Agriș.',
						)
					),
					$this->widget( 'accessibility-widget', 'agris-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				)
			),
		);
	}

	private function section( string $seed, string $kicker, string $title, string $description = '', string $button = '', string $url = '' ): array {
		return $this->container(
			$seed,
			array(
				$this->widget(
					$seed . '-widget',
					'agris-section-heading',
					array(
						'kicker'      => $kicker,
						'title'       => $title,
						'description' => $description,
						'button_text' => $button,
						'button_link' => $this->link( $url ),
					)
				),
			),
			array(
				'padding' => array( 'unit' => 'px', 'top' => '80', 'right' => '0', 'bottom' => '30', 'left' => '0', 'isLinked' => false ),
			)
		);
	}
}
