<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Template_Applier {
	private const NONCE_ACTION = 'agris_apply_template';
	private const NONCE_RESTORE = 'agris_restore_template';
	private const BACKUP_META = '_agris_rebuild_backups';
	private const MANIFEST_META = '_agris_rebuild_manifest';
	private const MAX_BACKUPS = 10;
	private const ELEMENTOR_META = array(
		'_elementor_edit_mode',
		'_elementor_template_type',
		'_elementor_version',
		'_elementor_page_settings',
		'_wp_page_template',
		'_elementor_data',
		'_elementor_css',
	);

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_post_agris_apply_template', array( $this, 'handle_apply' ) );
		add_action( 'admin_post_agris_restore_template', array( $this, 'handle_restore' ) );
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

		$page_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		$status  = isset( $_GET['agris_status'] ) ? sanitize_key( wp_unslash( $_GET['agris_status'] ) ) : '';
		$error_code = isset( $_GET['agris_error'] ) ? sanitize_key( wp_unslash( $_GET['agris_error'] ) ) : '';
		$routes  = $this->routes();
		$targets = $this->template_targets();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Comuna Agriș rebuild', 'comuna-agris' ); ?></h1>
			<?php if ( 'applied' === $status ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Șablonul Elementor a fost aplicat fără schimbarea adresei paginii.', 'comuna-agris' ); ?></p></div>
			<?php elseif ( 'restored' === $status ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Ultima copie de siguranță a paginii a fost restaurată.', 'comuna-agris' ); ?></p></div>
			<?php elseif ( 'error' === $status ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Operațiunea nu a putut fi finalizată. Pagina nu a fost modificată.', 'comuna-agris' ); ?><?php if ( $error_code ) : ?> <code><?php echo esc_html( $error_code ); ?></code><?php endif; ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Instrumentul păstrează ID-ul, titlul, limba, părintele, slugul și adresa URL. Înainte de fiecare aplicare salvează automat starea curentă.', 'comuna-agris' ); ?></p>
			<table class="widefat striped" style="max-width:980px">
				<thead><tr><th><?php esc_html_e( 'Șablon', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Pagină țintă', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Siguranță', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Acțiune', 'comuna-agris' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( $targets as $template => $target ) :
						$post = $target['post_id'] ? get_post( $target['post_id'] ) : null;
						$backups = $post ? $this->backups( (int) $post->ID ) : array();
						$manifest = $post ? get_post_meta( (int) $post->ID, self::MANIFEST_META, true ) : array();
						?>
					<tr<?php echo $page_id && $post && $page_id === (int) $post->ID ? ' style="box-shadow:inset 4px 0 #2271b1"' : ''; ?>>
						<th scope="row"><?php echo esc_html( $target['label'] ); ?></th>
						<td><?php if ( $post ) : ?><strong><?php echo esc_html( get_the_title( $post ) ); ?></strong><br><code><?php echo esc_html( get_permalink( $post ) ); ?></code><?php else : ?><span style="color:#b32d2e"><?php esc_html_e( 'Pagina nu a fost găsită automat.', 'comuna-agris' ); ?></span><?php endif; ?></td>
						<td><?php echo esc_html( sprintf( __( '%d copii disponibile', 'comuna-agris' ), count( $backups ) ) ); ?><?php if ( is_array( $manifest ) && ! empty( $manifest['applied_at'] ) ) : ?><br><small><?php echo esc_html( sprintf( __( 'Ultima aplicare: %s, versiunea %s', 'comuna-agris' ), $manifest['applied_at'], $manifest['version'] ?? '' ) ); ?></small><?php endif; ?></td>
						<td>
							<?php if ( $post && current_user_can( 'edit_post', $post->ID ) ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:8px">
									<input type="hidden" name="action" value="agris_apply_template">
									<input type="hidden" name="template" value="<?php echo esc_attr( $template ); ?>">
									<input type="hidden" name="post_id" value="<?php echo (int) $post->ID; ?>">
									<?php wp_nonce_field( self::NONCE_ACTION . '_' . (int) $post->ID ); ?>
									<?php submit_button( $target['button'], 'primary', 'submit', false ); ?>
								</form>
								<?php if ( $backups ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block">
										<input type="hidden" name="action" value="agris_restore_template">
										<input type="hidden" name="post_id" value="<?php echo (int) $post->ID; ?>">
										<?php wp_nonce_field( self::NONCE_RESTORE . '_' . (int) $post->ID ); ?>
										<?php submit_button( __( 'Restaurează versiunea anterioară', 'comuna-agris' ), 'secondary', 'submit', false ); ?>
									</form>
								<?php endif; ?>
							<?php else : ?><button class="button button-primary" disabled><?php echo esc_html( $target['button'] ); ?></button><?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Rute folosite de șablon', 'comuna-agris' ); ?></h2>
			<table class="widefat striped" style="max-width:820px"><tbody><?php foreach ( $routes as $key => $route ) : ?><tr><th scope="row"><?php echo esc_html( $key ); ?></th><td><code><?php echo esc_html( $route ); ?></code></td></tr><?php endforeach; ?></tbody></table>
		</div>
		<?php
	}

	public function handle_apply(): void {
		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$template = isset( $_POST['template'] ) ? sanitize_key( wp_unslash( $_POST['template'] ) ) : '';

		if ( ! $post_id || ! check_admin_referer( self::NONCE_ACTION . '_' . $post_id ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'comuna-agris' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Nu aveți permisiunea necesară.', 'comuna-agris' ) );
		}

		$targets = $this->template_targets();
		if ( ! isset( $targets[ $template ] ) || $post_id !== (int) $targets[ $template ]['post_id'] ) {
			wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&post_id=' . $post_id . '&agris_status=error' ) );
			exit;
		}

		$result     = $this->apply_template( $post_id, $template );
		$status     = is_wp_error( $result ) ? 'error' : 'applied';
		$error_code = is_wp_error( $result ) ? sanitize_key( $result->get_error_code() ) : '';

		wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&post_id=' . $post_id . '&agris_status=' . $status . ( $error_code ? '&agris_error=' . rawurlencode( $error_code ) : '' ) ) );
		exit;
	}

	public function handle_restore(): void {
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id || ! check_admin_referer( self::NONCE_RESTORE . '_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'comuna-agris' ) );
		}

		$status = $this->restore_latest( $post_id ) ? 'restored' : 'error';
		wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&post_id=' . $post_id . '&agris_status=' . $status ) );
		exit;
	}

	private function apply_template( int $post_id, string $template ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'invalid_page', __( 'Pagina țintă nu este validă.', 'comuna-agris' ) );
		}

		$data = 'home_ro' === $template ? $this->home_ro_data() : ( 'mayor_ro' === $template ? $this->mayor_ro_data() : array() );
		if ( ! $data ) {
			return new \WP_Error( 'invalid_template', __( 'Șablonul solicitat nu este disponibil.', 'comuna-agris' ) );
		}
		$encoded_data = wp_json_encode( $data );
		if ( false === $encoded_data ) {
			return new \WP_Error( 'elementor_json_failed', __( 'Datele Elementor nu au putut fi codificate.', 'comuna-agris' ) );
		}

		$permalink_before = get_permalink( $post_id );
		$this->save_backup( $post_id, 'before_' . $template );
		wp_save_post_revision( $post_id );

		$updated = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $updated ) ) {
			$this->restore_latest( $post_id );
			return $updated;
		}

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.33.0' );
		update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( $encoded_data ) );

		delete_post_meta( $post_id, '_elementor_css' );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		clean_post_cache( $post_id );
		if ( untrailingslashit( (string) $permalink_before ) !== untrailingslashit( (string) get_permalink( $post_id ) ) ) {
			$this->restore_latest( $post_id );
			return new \WP_Error( 'url_changed', __( 'Adresa URL s-a modificat; schimbarea a fost anulată automat.', 'comuna-agris' ) );
		}

		update_post_meta(
			$post_id,
			self::MANIFEST_META,
			array(
				'template'   => $template,
				'version'    => AGRIS_WIDGETS_VERSION,
				'applied_at' => current_time( 'mysql' ),
				'url'        => $permalink_before,
				'hash'       => hash( 'sha256', wp_json_encode( $data ) ),
			)
		);

		return true;
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

	private function find_mayor_ro_page(): int {
		return $this->find_ro_page( array( 'primar' ) );
	}

	private function find_ro_page( array $slugs ): int {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => array( 'publish', 'draft', 'private' ),
					'posts_per_page'   => -1,
					'suppress_filters' => false,
				)
			);
			foreach ( $pages as $page ) {
				$language  = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( 'ro' === $language || ( ! $language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/ro/' ) ) ) {
					return (int) $page->ID;
				}
			}
		}
		return 0;
	}

	private function template_targets(): array {
		return array(
			'home_ro'  => array(
				'label'   => __( 'Index român', 'comuna-agris' ),
				'button'  => __( 'Aplică indexul român Elementor', 'comuna-agris' ),
				'post_id' => $this->find_home_ro_page(),
			),
			'mayor_ro' => array(
				'label'   => __( 'Pagina Primar', 'comuna-agris' ),
				'button'  => __( 'Aplică pagina Primar Elementor', 'comuna-agris' ),
				'post_id' => $this->find_mayor_ro_page(),
			),
		);
	}

	private function menu_id(): string {
		$menus = wp_get_nav_menus();
		if ( ! $menus ) {
			return '';
		}

		$best_id = '';
		$best_score = -1;
		foreach ( $menus as $menu ) {
			$name = strtolower( remove_accents( $menu->name ) );
			$items = wp_get_nav_menu_items( $menu->term_id );
			$count = is_array( $items ) ? count( $items ) : 0;
			if ( 0 === $count ) {
				continue;
			}

			$score = $count;
			if ( in_array( $name, array( 'fo roman', 'fő roman', 'fo romana', 'meniu principal roman' ), true ) ) {
				$score += 10000;
			} elseif ( ( str_contains( $name, 'fo' ) || str_contains( $name, 'principal' ) ) && ( str_contains( $name, 'roman' ) || str_contains( $name, 'ro' ) ) ) {
				$score += 5000;
			} elseif ( str_contains( $name, 'roman' ) ) {
				$score += 1000;
			}
			if ( str_contains( $name, 'bal' ) || str_contains( $name, 'sidebar' ) ) {
				$score -= 5000;
			}

			if ( $score > $best_score ) {
				$best_score = $score;
				$best_id = (string) $menu->term_id;
			}
		}

		return $best_id;
	}

	private function backups( int $post_id ): array {
		$backups = get_post_meta( $post_id, self::BACKUP_META, true );
		return is_array( $backups ) ? $backups : array();
	}

	private function save_backup( int $post_id, string $reason ): void {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$meta = array();
		foreach ( self::ELEMENTOR_META as $key ) {
			$meta[ $key ] = array(
				'exists' => metadata_exists( 'post', $post_id, $key ),
				'value'  => get_post_meta( $post_id, $key, true ),
			);
		}

		$backups = $this->backups( $post_id );
		$backups[] = array(
			'created_at'  => current_time( 'mysql' ),
			'reason'      => $reason,
			'post_content'=> $post->post_content,
			'permalink'   => get_permalink( $post_id ),
			'meta'        => $meta,
		);
		$backups = array_slice( $backups, -self::MAX_BACKUPS );
		update_post_meta( $post_id, self::BACKUP_META, $backups );
	}

	private function restore_latest( int $post_id ): bool {
		$backups = $this->backups( $post_id );
		$backup = array_pop( $backups );
		if ( ! is_array( $backup ) ) {
			return false;
		}

		$result = wp_update_post( array( 'ID' => $post_id, 'post_content' => (string) ( $backup['post_content'] ?? '' ) ), true );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		foreach ( self::ELEMENTOR_META as $key ) {
			$state = $backup['meta'][ $key ] ?? array( 'exists' => false, 'value' => '' );
			if ( empty( $state['exists'] ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				$value = $state['value'] ?? '';
				update_post_meta( $post_id, $key, is_string( $value ) ? wp_slash( $value ) : $value );
			}
		}
		update_post_meta( $post_id, self::BACKUP_META, $backups );
		delete_post_meta( $post_id, self::MANIFEST_META );
		clean_post_cache( $post_id );
		return true;
	}

	private function routes(): array {
		return array(
			'home_ro'       => $this->page_url( array( 'home-ro' ), '/ro/home-ro/' ),
			'home_hu'       => $this->page_url( array( 'home-hu' ), '/hu/home-hu/' ),
			'monitor'       => $this->page_url( array( 'monitorul-oficial-local' ), '/ro/monitorul-oficial-local/' ),
			'contact'       => $this->page_url( array( 'contact' ), '/ro/contact/' ),
			'announcements' => $this->page_url( array( 'anunti' ), '/ro/anunti/' ),
			'decisions'     => $this->page_url( array( 'hotarari-ale-consiului-local' ), '/ro/hotarari-ale-consiului-local/' ),
			'forms'         => $this->page_url( array( 'formulare-tipizate' ), '/ro/formulare-tipizate/' ),
			'taxes'         => $this->page_url( array( 'taxe-si-impozite-locale' ), '/ro/taxe-si-impozite-locale/' ),
			'urbanism'      => $this->page_url( array( 'urbanism' ), '/ro/urbanism/' ),
			'agricultural'  => $this->page_url( array( 'registru-agricol' ), '/ro/registru-agricol/' ),
			'mayor'         => $this->page_url( array( 'primar' ), '/ro/primar/' ),
			'council'       => $this->page_url( array( 'conponenta-consiliul-local' ), '/ro/conponenta-consiliul-local/' ),
		);
	}

	private function page_url( array $slugs, string $fallback ): string {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => 'publish',
					'posts_per_page'   => -1,
					'suppress_filters' => false,
				)
			);
			foreach ( $pages as $page ) {
				$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( 'ro' === $language || ( ! $language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/ro/' ) ) ) {
					return $permalink;
				}
			}
		}
		return home_url( $fallback );
	}

	private function translated_url( int $post_id, string $language, string $fallback ): string {
		if ( $post_id && function_exists( 'pll_get_post' ) ) {
			$translated_id = (int) pll_get_post( $post_id, $language );
			if ( $translated_id && 'publish' === get_post_status( $translated_id ) ) {
				return get_permalink( $translated_id );
			}
		}
		return $fallback;
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
					'content_width' => 'boxed',
					'boxed_width'   => array( 'unit' => 'px', 'size' => 1240, 'sizes' => array() ),
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
		$routes  = $this->routes();

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
							'home_url'       => $this->link( $routes['home_ro'] ),
							'menu_id'        => $menu_id,
							'cta_text'       => 'Monitorul Oficial',
							'cta_link'       => $this->link( $routes['monitor'] ),
							'sticky'         => 'yes',
							'language_items' => $this->repeater( 'lang', array(
								array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $routes['home_ro'] ) ),
								array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $routes['home_hu'] ) ),
							) ),
						)
					),
					$this->widget( 'search-modal', 'agris-search-box' ),
				),
				array( 'content_width' => 'full' )
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
							'primary_link'   => $this->link( $routes['monitor'] ),
							'secondary_text' => 'Contact rapid',
							'secondary_link' => $this->link( $routes['contact'] ),
							'background'     => $this->media(),
							'show_search'    => 'yes',
							'updates_title'  => 'Noutăți din portal',
							'updates_items'  => $this->repeater( 'updates', array(
								array( 'day' => 'AN', 'title' => 'Anunțuri publice', 'meta' => 'Actualizate periodic', 'url' => $this->link( $routes['announcements'] ) ),
								array( 'day' => 'HCL', 'title' => 'Hotărâri Consiliul Local', 'meta' => 'Arhivă și documente', 'url' => $this->link( $routes['decisions'] ) ),
								array( 'day' => 'DOC', 'title' => 'Documente administrative', 'meta' => 'Formulare și registre', 'url' => $this->link( $routes['monitor'] ) ),
							) ),
						)
					),
				),
				array( 'content_width' => 'full' )
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
								array( 'icon' => 'TAX', 'title' => 'Taxe și impozite', 'description' => 'Informații pentru plăți, evidențe fiscale și program.', 'url' => $this->link( $routes['taxes'] ) ),
								array( 'icon' => 'DOC', 'title' => 'Formulare', 'description' => 'Documente tipizate și cereri pentru cetățeni.', 'url' => $this->link( $routes['forms'] ) ),
								array( 'icon' => 'URB', 'title' => 'Urbanism', 'description' => 'Certificate, autorizații și informații urbanistice.', 'url' => $this->link( $routes['urbanism'] ) ),
								array( 'icon' => 'AGR', 'title' => 'Registru agricol', 'description' => 'Servicii și evidențe pentru gospodării și terenuri.', 'url' => $this->link( $routes['agricultural'] ) ),
							) ),
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'news-head', 'Actualități', 'Noutăți și anunțuri', 'Cele mai recente comunicări publicate de Primăria Comunei Agriș.', 'Toate noutățile', $routes['announcements'] ),
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
							'source'       => 'automatic',
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
							'button_link' => $this->link( $routes['contact'] ),
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
								array( 'column' => 'Primăria', 'label' => 'Conducere', 'url' => $this->link( $routes['mayor'] ) ),
								array( 'column' => 'Primăria', 'label' => 'Consiliul Local', 'url' => $this->link( $routes['council'] ) ),
								array( 'column' => 'Informații publice', 'label' => 'Anunțuri', 'url' => $this->link( $routes['announcements'] ) ),
								array( 'column' => 'Informații publice', 'label' => 'Monitorul Oficial', 'url' => $this->link( $routes['monitor'] ) ),
							) ),
							'phone'       => '0261 878 112',
							'email'       => 'primaria@comunaagris.ro',
							'address'     => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare',
							'copyright'   => 'Toate drepturile rezervate Comuna Agriș.',
						)
					),
					$this->widget( 'accessibility-widget', 'agris-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				),
				array( 'content_width' => 'full' )
			),
		);
	}

	private function mayor_ro_data(): array {
		$menu_id = $this->menu_id();
		$routes  = $this->routes();
		$mayor_hu = $this->translated_url( $this->find_mayor_ro_page(), 'hu', $routes['home_hu'] );

		return array(
			$this->container(
				'mayor-header',
				array(
					$this->widget(
						'mayor-header-widget',
						'agris-site-header',
						array(
							'official_text'  => 'Site oficial al Primăriei Comunei Agriș, județul Satu Mare, România',
							'trust_text'     => 'Conexiune securizată',
							'mail_url'       => $this->link( 'mailto:primaria@comunaagris.ro' ),
							'logo'           => $this->media(),
							'brand_title'    => 'Comuna Agriș',
							'brand_subtitle' => 'Primăria Comunei Agriș',
							'home_url'       => $this->link( $routes['home_ro'] ),
							'menu_id'        => $menu_id,
							'cta_text'       => 'Monitorul Oficial',
							'cta_link'       => $this->link( $routes['monitor'] ),
							'sticky'         => 'yes',
							'language_items' => $this->repeater( 'mayor-lang', array(
								array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $routes['mayor'] ) ),
								array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $mayor_hu ) ),
							) ),
						)
					),
					$this->widget( 'mayor-search-modal', 'agris-search-box' ),
				),
				array( 'content_width' => 'full' )
			),
			$this->container(
				'mayor-hero',
				array(
					$this->widget(
						'mayor-hero-widget',
						'agris-page-hero',
						array(
							'kicker'        => 'Conducerea primăriei',
							'title'         => 'Primarul Comunei Agriș',
							'description'   => 'Date de contact, informații publice și programul de audiențe al primarului.',
							'parent_label'  => 'Acasă',
							'parent_link'   => $this->link( $routes['home_ro'] ),
							'current_label' => 'Primar',
							'background'    => $this->media(),
						)
					),
				),
				array( 'content_width' => 'full' )
			),
			$this->container(
				'mayor-profile',
				array(
					$this->widget(
						'mayor-profile-widget',
						'agris-person-profile',
						array(
							'photo'    => $this->media(),
							'role'     => 'Primar',
							'name'     => 'Szabo Elek',
							'subtitle' => 'Primarul Comunei Agriș',
							'bio'      => '<p><strong>Data nașterii:</strong> 03.10.1963</p><p>Primarul reprezintă comuna în relația cu cetățenii și coordonează activitatea administrației publice locale.</p>',
							'phone'    => '0261 878 111',
							'email'    => 'primar@comunaagris.ro',
							'office'   => 'Luni 9:00–11:00 · Joi 9:00–11:00',
						)
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '70', 'right' => '0', 'bottom' => '36', 'left' => '0', 'isLinked' => false ) )
			),
			$this->container(
				'mayor-schedule',
				array(
					$this->widget(
						'mayor-schedule-widget',
						'agris-schedule-grid',
						array(
							'kicker'      => 'Program audiențe',
							'title'       => 'Întâlniri cu cetățenii',
							'description' => 'Pentru o organizare mai bună, vă recomandăm să confirmați telefonic audiența.',
							'items_list'  => $this->repeater( 'mayor-schedule-items', array(
								array( 'icon' => 'LU', 'title' => 'Luni', 'time' => '09:00–11:00' ),
								array( 'icon' => 'JO', 'title' => 'Joi', 'time' => '09:00–11:00' ),
							) ),
						)
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) )
			),
			$this->container(
				'mayor-cta',
				array(
					$this->widget(
						'mayor-cta-widget',
						'agris-cta-banner',
						array(
							'kicker'      => 'Relații cu cetățenii',
							'title'       => 'Aveți nevoie de informații suplimentare?',
							'description' => 'Contactați Primăria Comunei Agriș pentru programări și informații administrative.',
							'image'       => $this->media(),
							'button_text' => 'Date de contact',
							'button_link' => $this->link( $routes['contact'] ),
						)
					),
				)
			),
			$this->container(
				'mayor-footer',
				array(
					$this->widget(
						'mayor-footer-widget',
						'agris-site-footer',
						array(
							'title'       => 'Comuna Agriș',
							'subtitle'    => 'Primăria Comunei Agriș',
							'description' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.',
							'links'       => $this->repeater( 'mayor-footer-links', array(
								array( 'column' => 'Primăria', 'label' => 'Conducere', 'url' => $this->link( $routes['mayor'] ) ),
								array( 'column' => 'Primăria', 'label' => 'Consiliul Local', 'url' => $this->link( $routes['council'] ) ),
								array( 'column' => 'Informații publice', 'label' => 'Anunțuri', 'url' => $this->link( $routes['announcements'] ) ),
								array( 'column' => 'Informații publice', 'label' => 'Monitorul Oficial', 'url' => $this->link( $routes['monitor'] ) ),
							) ),
							'phone'       => '0261 878 112',
							'email'       => 'primaria@comunaagris.ro',
							'address'     => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare',
							'copyright'   => 'Toate drepturile rezervate Comuna Agriș.',
						)
					),
					$this->widget( 'mayor-accessibility-widget', 'agris-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				),
				array( 'content_width' => 'full' )
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
