<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Template_Applier {
	private const NONCE_ACTION = 'agris_apply_template';
	private const NONCE_RESTORE = 'agris_restore_template';
	private const NONCE_GROUP = 'agris_apply_group';
	private const NONCE_ALL = 'agris_apply_all';
	private const NONCE_ALL_HU = 'agris_apply_all_hu';
	private const BACKUP_META = '_agris_rebuild_backups';
	private const MANIFEST_META = '_agris_rebuild_manifest';
	private const SOURCE_META = '_agris_original_page_content';
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
	private array $menu_id_cache = array();
	private array $routes_cache = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_post_agris_apply_template', array( $this, 'handle_apply' ) );
		add_action( 'admin_post_agris_restore_template', array( $this, 'handle_restore' ) );
		add_action( 'admin_post_agris_apply_group', array( $this, 'handle_apply_group' ) );
		add_action( 'admin_post_agris_apply_all', array( $this, 'handle_apply_all' ) );
		add_action( 'admin_post_agris_apply_all_hu', array( $this, 'handle_apply_all_hu' ) );
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
		$error_summary = isset( $_GET['agris_error_summary'] ) ? sanitize_text_field( wp_unslash( $_GET['agris_error_summary'] ) ) : '';
		$group_done = isset( $_GET['agris_group'] ) ? sanitize_key( wp_unslash( $_GET['agris_group'] ) ) : '';
		$group_ok = isset( $_GET['agris_ok'] ) ? absint( $_GET['agris_ok'] ) : 0;
		$group_failed = isset( $_GET['agris_failed'] ) ? absint( $_GET['agris_failed'] ) : 0;
		$all_done = isset( $_GET['agris_all'] ) ? absint( $_GET['agris_all'] ) : 0;
		$routes  = array( 'ro' => $this->routes( 'ro' ), 'hu' => $this->routes( 'hu' ) );
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
			<?php if ( $group_done ) : ?><div class="notice <?php echo $group_failed ? 'notice-warning' : 'notice-success'; ?>"><p><?php echo esc_html( sprintf( __( 'Grupul %1$s: %2$d pagini reconstruite, %3$d erori. URL-urile au fost verificate individual.', 'comuna-agris' ), $group_done, $group_ok, $group_failed ) ); ?></p></div><?php endif; ?>
			<?php if ( $all_done ) : ?><div class="notice <?php echo $group_failed ? 'notice-warning' : 'notice-success'; ?>"><p><?php echo esc_html( sprintf( 'Reconstrucție completă: %1$d pagini reconstruite, %2$d erori. Fiecare adresă URL a fost verificată.', $group_ok, $group_failed ) ); ?></p></div><?php endif; ?>
			<?php if ( $error_summary ) : ?><div class="notice notice-info"><p><?php echo esc_html( 'Coduri de eroare: ' . $error_summary ); ?></p></div><?php endif; ?>
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
			<h2 style="margin-top:28px"><?php esc_html_e( 'Reconstrucție în grupuri', 'comuna-agris' ); ?></h2>
			<p><?php esc_html_e( 'Sunt incluse separat paginile române și maghiare publicate. Paginile private și ciornele nu sunt modificate. Fiecare pagină primește propria copie de siguranță.', 'comuna-agris' ); ?></p>
			<table class="widefat striped" style="max-width:980px"><thead><tr><th><?php esc_html_e( 'Grup', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Pagini', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Descriere', 'comuna-agris' ); ?></th><th><?php esc_html_e( 'Acțiune', 'comuna-agris' ); ?></th></tr></thead><tbody>
			<?php foreach ( $this->rebuild_groups() as $group => $definition ) : $group_pages = $this->group_pages( $group ); ?>
				<tr><th scope="row"><?php echo esc_html( $definition['label'] ); ?></th><td><?php echo (int) count( $group_pages ); ?></td><td><?php echo esc_html( $definition['description'] ); ?></td><td><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="agris_apply_group"><input type="hidden" name="group" value="<?php echo esc_attr( $group ); ?>"><?php wp_nonce_field( self::NONCE_GROUP . '_' . $group ); ?><?php submit_button( sprintf( __( 'Reconstruiește grupul (%d)', 'comuna-agris' ), count( $group_pages ) ), 'secondary', 'submit', false, count( $group_pages ) ? array() : array( 'disabled' => 'disabled' ) ); ?></form></td></tr>
			<?php endforeach; ?>
			</tbody></table>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Reconstrucție completă', 'comuna-agris' ); ?></h2>
			<p><?php esc_html_e( 'Aplicați separat sistemul Elementor pe paginile române sau maghiare publicate, fără schimbarea adreselor.', 'comuna-agris' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="agris_apply_all"><?php wp_nonce_field( self::NONCE_ALL ); ?><?php submit_button( sprintf( __( 'Reconstruiește toate paginile române (%d)', 'comuna-agris' ), count( $this->published_ro_pages() ) + count( array_filter( wp_list_pluck( $this->template_targets( 'ro' ), 'post_id' ) ) ) ), 'primary', 'submit', false ); ?></form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px"><input type="hidden" name="action" value="agris_apply_all_hu"><?php wp_nonce_field( self::NONCE_ALL_HU ); ?><?php submit_button( sprintf( __( 'Reconstruiește toate paginile maghiare (%d)', 'comuna-agris' ), count( $this->published_hu_pages() ) + count( array_filter( wp_list_pluck( $this->template_targets( 'hu' ), 'post_id' ) ) ) ), 'primary', 'submit', false ); ?></form>
			<h2 style="margin-top:28px"><?php esc_html_e( 'Rute folosite de șablon', 'comuna-agris' ); ?></h2>
			<table class="widefat striped" style="max-width:820px"><tbody><?php foreach ( $routes as $language => $language_routes ) : ?><?php foreach ( $language_routes as $key => $route ) : ?><tr><th scope="row"><?php echo esc_html( strtoupper( $language ) . ' · ' . $key ); ?></th><td><code><?php echo esc_html( $route ); ?></code></td></tr><?php endforeach; ?><?php endforeach; ?></tbody></table>
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

	public function handle_apply_group(): void {
		$group = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';
		$groups = $this->rebuild_groups();
		if ( ! isset( $groups[ $group ] ) || ! check_admin_referer( self::NONCE_GROUP . '_' . $group ) || ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'comuna-agris' ) );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 180 );
		}

		$ok = 0;
		$failed = 0;
		$language = str_starts_with( $group, 'hu_' ) ? 'hu' : 'ro';
		foreach ( $this->group_pages( $group ) as $page ) {
			if ( ! current_user_can( 'edit_post', $page->ID ) ) {
				++$failed;
				continue;
			}
			$type = $this->classify_page( $page );
			$result = $this->write_elementor_page( $page, 'page_' . $language . '_' . $type, $this->generic_page_data( $page, $type, $language ) );
			if ( is_wp_error( $result ) ) {
				++$failed;
			} else {
				++$ok;
			}
		}

		wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&agris_group=' . rawurlencode( $group ) . '&agris_ok=' . $ok . '&agris_failed=' . $failed ) );
		exit;
	}

	public function handle_apply_all(): void {
		$this->handle_apply_all_language( 'ro', self::NONCE_ALL );
	}

	public function handle_apply_all_hu(): void {
		$this->handle_apply_all_language( 'hu', self::NONCE_ALL_HU );
	}

	private function handle_apply_all_language( string $language, string $nonce ): void {
		if ( ! check_admin_referer( $nonce ) || ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Cerere invalidă.', 'comuna-agris' ) );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 600 );
		}

		$ok = 0;
		$failed = 0;
		$error_codes = array();
		foreach ( $this->template_targets( $language ) as $template => $target ) {
			$post_id = (int) $target['post_id'];
			$result = $post_id && current_user_can( 'edit_post', $post_id ) ? $this->apply_template( $post_id, $template ) : new \WP_Error( 'permission_denied' );
			if ( is_wp_error( $result ) ) {
				++$failed;
				$code = $result->get_error_code();
				$error_codes[ $code ] = ( $error_codes[ $code ] ?? 0 ) + 1;
			} else {
				++$ok;
			}
		}

		foreach ( $this->published_pages( $language ) as $page ) {
			if ( ! current_user_can( 'edit_post', $page->ID ) ) {
				++$failed;
				$error_codes['permission_denied'] = ( $error_codes['permission_denied'] ?? 0 ) + 1;
				continue;
			}
			$type = $this->classify_page( $page );
			$result = $this->write_elementor_page( $page, 'page_' . $language . '_' . $type, $this->generic_page_data( $page, $type, $language ) );
			if ( is_wp_error( $result ) ) {
				++$failed;
				$code = $result->get_error_code();
				$error_codes[ $code ] = ( $error_codes[ $code ] ?? 0 ) + 1;
			} else {
				++$ok;
			}
		}

		$summary = implode( ', ', array_map( static fn( $code, $count ): string => $code . ':' . $count, array_keys( $error_codes ), $error_codes ) );
		wp_safe_redirect( admin_url( 'tools.php?page=agris-rebuild&agris_all=1&agris_language=' . rawurlencode( $language ) . '&agris_ok=' . $ok . '&agris_failed=' . $failed . '&agris_error_summary=' . rawurlencode( $summary ) ) );
		exit;
	}

	private function apply_template( int $post_id, string $template ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'invalid_page', __( 'Pagina țintă nu este validă.', 'comuna-agris' ) );
		}

		$data = match ( $template ) {
			'home_ro'  => $this->home_ro_data( $post ),
			'mayor_ro' => $this->mayor_ro_data( $post ),
			'home_hu'  => $this->home_hu_data( $post ),
			'mayor_hu' => $this->mayor_hu_data( $post ),
			default    => array(),
		};
		if ( ! $data ) {
			return new \WP_Error( 'invalid_template', __( 'Șablonul solicitat nu este disponibil.', 'comuna-agris' ) );
		}

		return $this->write_elementor_page( $post, $template, $data );
	}

	private function write_elementor_page( \WP_Post $post, string $template, array $data ) {
		$post_id = (int) $post->ID;
		$encoded_data = wp_json_encode( $data );
		if ( false === $encoded_data ) {
			return new \WP_Error( 'elementor_json_failed', __( 'Datele Elementor nu au putut fi codificate.', 'comuna-agris' ) );
		}

		$permalink_before = get_permalink( $post_id );
		if ( ! metadata_exists( 'post', $post_id, self::SOURCE_META ) && '' !== trim( (string) $post->post_content ) ) {
			update_post_meta( $post_id, self::SOURCE_META, wp_slash( $post->post_content ) );
		}
		$this->save_backup( $post_id, 'before_' . $template );
		wp_save_post_revision( $post_id );

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

	private function find_home_hu_page(): int {
		return $this->find_language_page( array( 'home-hu' ), 'hu' );
	}

	private function find_mayor_hu_page(): int {
		$translated = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $this->find_mayor_ro_page(), 'hu' ) : 0;
		return $translated ?: $this->find_language_page( array( 'polgarmester' ), 'hu' );
	}

	private function find_ro_page( array $slugs ): int {
		return $this->find_language_page( $slugs, 'ro' );
	}

	private function find_language_page( array $slugs, string $language ): int {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => array( 'publish', 'draft', 'private' ),
					'posts_per_page'   => -1,
					'suppress_filters' => false,
					'lang'             => $language,
				)
			);
			foreach ( $pages as $page ) {
				$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( $language === $page_language || ( ! $page_language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' . $language . '/' ) ) ) {
					return (int) $page->ID;
				}
			}
		}
		return 0;
	}

	private function template_targets( string $language = '' ): array {
		$targets = array(
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
			'home_hu'  => array(
				'label'   => __( 'Magyar index', 'comuna-agris' ),
				'button'  => __( 'A magyar Elementor-index alkalmazása', 'comuna-agris' ),
				'post_id' => $this->find_home_hu_page(),
			),
			'mayor_hu' => array(
				'label'   => __( 'Polgármester oldal', 'comuna-agris' ),
				'button'  => __( 'A magyar polgármesteri oldal alkalmazása', 'comuna-agris' ),
				'post_id' => $this->find_mayor_hu_page(),
			),
		);
		if ( in_array( $language, array( 'ro', 'hu' ), true ) ) {
			return array_filter( $targets, static fn( string $key ): bool => str_ends_with( $key, '_' . $language ), ARRAY_FILTER_USE_KEY );
		}
		return $targets;
	}

	private function rebuild_groups(): array {
		$groups = array(
			'administration' => array(
				'label'       => __( 'Primărie și conducere', 'comuna-agris' ),
				'description' => __( 'Conducere, consiliu local, departamente și pagina de contact.', 'comuna-agris' ),
			),
			'documents'      => array(
				'label'       => __( 'Documente și transparență', 'comuna-agris' ),
				'description' => __( 'Hotărâri, anunțuri, proiecte, declarații, bugete, registre și formulare.', 'comuna-agris' ),
			),
			'community'      => array(
				'label'       => __( 'Comuna și servicii', 'comuna-agris' ),
				'description' => __( 'Istorie, localizare, servicii, turism, sport, personalități și pagini informative.', 'comuna-agris' ),
			),
			'galleries'      => array(
				'label'       => __( 'Galerii', 'comuna-agris' ),
				'description' => __( 'Pagini foto și galerii istorice.', 'comuna-agris' ),
			),
		);
		$hungarian = array(
			'administration' => array( 'label' => 'Magyar · Polgármesteri hivatal és vezetőség', 'description' => 'Polgármester, helyi tanács, hivatali részlegek és elérhetőségek.' ),
			'documents'      => array( 'label' => 'Magyar · Dokumentumok és közérdekű adatok', 'description' => 'Felhívások, határozatok, közlöny, szabályzatok, nyilvántartások és formanyomtatványok.' ),
			'community'      => array( 'label' => 'Magyar · Község és szolgáltatások', 'description' => 'Történet, elhelyezkedés, turizmus, sport, kultúra és közösségi oldalak.' ),
			'galleries'      => array( 'label' => 'Magyar · Galériák', 'description' => 'Fényképes oldalak, események és történelmi galériák.' ),
		);
		foreach ( $hungarian as $key => $definition ) {
			$groups['hu_' . $key] = $definition;
		}
		return $groups;
	}

	private function published_ro_pages(): array {
		return $this->published_pages( 'ro' );
	}

	private function published_hu_pages(): array {
		return $this->published_pages( 'hu' );
	}

	private function published_pages( string $language ): array {
		$pages = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'suppress_filters' => false,
				'lang'             => $language,
			)
		);
		$excluded = 'hu' === $language
			? array_filter( array( $this->find_home_hu_page(), $this->find_mayor_hu_page() ) )
			: array_filter( array( $this->find_home_ro_page(), $this->find_mayor_ro_page() ) );
		return array_values(
			array_filter(
				$pages,
				function ( $page ) use ( $excluded, $language ): bool {
					if ( in_array( (int) $page->ID, $excluded, true ) ) {
						return false;
					}
					$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
					$path = (string) wp_parse_url( get_permalink( $page ), PHP_URL_PATH );
					return $language === $page_language || ( ! $page_language && str_contains( $path, '/' . $language . '/' ) );
				}
			)
		);
	}

	private function classify_page( \WP_Post $page ): string {
		$slug = strtolower( remove_accents( $page->post_name ) );
		$title = strtolower( remove_accents( $page->post_title ) );
		$haystack = $slug . ' ' . $title;

		if ( preg_match( '/galeria|gallery|foto|fénykép|fenykep/', $haystack ) ) {
			return 'galleries';
		}
		if ( preg_match( '/contact|viceprimar|secretar|departament|consili|cuvantul-primarului|elerhet|alpolgarmester|jegyzo|reszleg|tanacs|polgarmester/', $haystack ) ) {
			return 'administration';
		}
		if ( preg_match( '/anunt|hotarar|document|declarati|buget|dare-de-seama|dispoziti|financiar|formular|legisl|monitorul-oficial|proces|proiect|registr|regulament|autorizati|certificat|taxe|impozit|convocator|minute|informare|statut|felhivas|hatarozat|nyilatkozat|koltsegvet|rendelkezes|penzugy|formanyomtatvany|jogalkotas|kozlony|jegyzokonyv|nyilvantartas|szabalyzat|torveny|kozerd/', $haystack ) ) {
			return 'documents';
		}
		return 'community';
	}

	private function group_pages( string $group ): array {
		$language = str_starts_with( $group, 'hu_' ) ? 'hu' : 'ro';
		$type = 'hu' === $language ? substr( $group, 3 ) : $group;
		return array_values(
			array_filter(
				$this->published_pages( $language ),
				fn( $page ): bool => $type === $this->classify_page( $page )
			)
		);
	}

	private function menu_id( string $language = 'ro' ): string {
		if ( isset( $this->menu_id_cache[ $language ] ) ) {
			return $this->menu_id_cache[ $language ];
		}
		$menus = wp_get_nav_menus();
		if ( ! $menus ) {
			$this->menu_id_cache[ $language ] = '';
			return $this->menu_id_cache[ $language ];
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
			$language_names = 'hu' === $language ? array( 'fo magyar', 'fő magyar', 'magyar fo menu', 'magyar fő menü' ) : array( 'fo roman', 'fő roman', 'fo romana', 'meniu principal roman' );
			$language_tokens = 'hu' === $language ? array( 'magyar', 'hungar', 'hu' ) : array( 'roman', 'romana', 'ro' );
			$opposite_tokens = 'hu' === $language ? array( 'roman', 'romana' ) : array( 'magyar', 'hungar' );
			if ( in_array( $name, $language_names, true ) ) {
				$score += 10000;
			} elseif ( ( str_contains( $name, 'fo' ) || str_contains( $name, 'fő' ) || str_contains( $name, 'principal' ) ) && array_filter( $language_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score += 5000;
			} elseif ( array_filter( $language_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score += 1000;
			}
			foreach ( (array) $items as $item ) {
				$path = (string) wp_parse_url( (string) ( $item->url ?? '' ), PHP_URL_PATH );
				$score += str_contains( $path, '/' . $language . '/' ) ? 120 : 0;
			}
			if ( array_filter( $opposite_tokens, fn( string $token ): bool => str_contains( $name, $token ) ) ) {
				$score -= 6000;
			}
			if ( str_contains( $name, 'bal' ) || str_contains( $name, 'sidebar' ) ) {
				$score -= 5000;
			}

			if ( $score > $best_score ) {
				$best_score = $score;
				$best_id = (string) $menu->term_id;
			}
		}

		$this->menu_id_cache[ $language ] = $best_id;
		return $this->menu_id_cache[ $language ];
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

	private function routes( string $language = 'ro' ): array {
		if ( isset( $this->routes_cache[ $language ] ) ) {
			return $this->routes_cache[ $language ];
		}
		if ( 'hu' === $language ) {
			$home_hu = $this->page_url( array( 'home-hu' ), '/hu/home-hu/', 'hu' );
			$ro_routes = $this->routes( 'ro' );
			$definitions = array(
				'public_info'   => array( array( 'informatii-publice' ), array() ),
				'monitor'       => array( array( 'monitorul-oficial-local' ), array( 'helyi-hivatalos-kozlony' ) ),
				'contact'       => array( array( 'contact' ), array( 'elerhetosegeink' ) ),
				'announcements' => array( array( 'anunti' ), array() ),
				'decisions'     => array( array( 'hotarari-ale-consiului-local' ), array( 'a-helyi-tanacs-hatarozatai', 'hatarozattervezetek' ) ),
				'forms'         => array( array( 'formulare-tipizate' ), array( 'formanyomtatvanyok' ) ),
				'taxes'         => array( array( 'taxe-si-impozite-locale' ), array() ),
				'urbanism'      => array( array( 'urbanism' ), array() ),
				'agricultural'  => array( array( 'registru-agricol' ), array() ),
				'mayor'         => array( array( 'primar' ), array( 'polgarmester' ) ),
				'council'       => array( array( 'conponenta-consiliul-local' ), array( 'a-helyi-tanacs-szerkezete' ) ),
				'history'       => array( array( 'istoria-comunei' ), array( 'kozsegunk-tortenete' ) ),
				'monuments'     => array( array( 'monumente-istorice' ), array( 'emlekmuvek' ) ),
				'tourism'       => array( array( 'ekoturisma' ), array( 'okoturizmus' ) ),
				'twinned'       => array( array( 'comune-infratite' ), array( 'testvertelepulesek' ) ),
				'deliberative'  => array( array( 'hotararile-autoritatii-deliberative' ), array( 'a-tanacsado-hatosag-rendelkezesei' ) ),
				'executive'     => array( array( 'dispozitii-autoritatii-executive' ), array( 'a-vegrehajto-hatosag-rendelkezesei' ) ),
				'financial'     => array( array( 'documente-si-informatii-financiare' ), array( 'penzugyi-dokumentumok-es-informaciok' ) ),
			);
			$routes = array( 'home_ro' => $this->page_url( array( 'home-ro' ), '/ro/home-ro/', 'ro' ), 'home_hu' => $home_hu );
			foreach ( $definitions as $key => $definition ) {
				$hu_page_id = $definition[1] ? $this->find_language_page( $definition[1], 'hu' ) : 0;
				$fallback = $hu_page_id && 'publish' === get_post_status( $hu_page_id ) ? (string) get_permalink( $hu_page_id ) : ( $ro_routes[ $key ] ?? $home_hu );
				if ( in_array( $key, array( 'public_info', 'announcements' ), true ) ) {
					$fallback = home_url( '/hu/category/felhivasok/' );
				}
				$routes[ $key ] = $this->translated_url( $this->find_ro_page( $definition[0] ), 'hu', $fallback );
			}
			$routes['location'] = $this->page_url( array( 'a-kozseg-elhelyezkedese' ), '/hu/a-kozseg-elhelyezkedese/', 'hu' );
			$routes['galleries'] = home_url( '/hu/category/foto-galeria/' );
			$routes['events'] = $this->page_url( array( 'esemenyek' ), '/hu/esemenyek/', 'hu' );
			$routes['journal'] = $this->page_url( array( 'egri-naplo' ), '/hu/egri-naplo/', 'hu' );
			$routes['law17'] = home_url( '/hu/category/17-es-torveny/' );
			$this->routes_cache['hu'] = $routes;
			return $routes;
		}
		$this->routes_cache['ro'] = array(
			'home_ro'       => $this->page_url( array( 'home-ro' ), '/ro/home-ro/' ),
			'home_hu'       => $this->page_url( array( 'home-hu' ), '/hu/home-hu/', 'hu' ),
			'public_info'   => $this->page_url( array( 'informatii-publice' ), '/ro/informatii-publice/' ),
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
			'history'       => $this->page_url( array( 'istoria-comunei' ), '/ro/istoria-comunei/' ),
			'monuments'     => $this->page_url( array( 'monumente-istorice' ), '/ro/monumente-istorice/' ),
			'tourism'       => $this->page_url( array( 'ekoturisma' ), '/ro/ekoturisma/' ),
			'twinned'       => $this->page_url( array( 'comune-infratite' ), '/ro/comune-infratite/' ),
			'deliberative'  => $this->page_url( array( 'hotararile-autoritatii-deliberative' ), '/ro/hotararile-autoritatii-deliberative/' ),
			'executive'     => $this->page_url( array( 'dispozitii-autoritatii-executive' ), '/ro/dispozitii-autoritatii-executive/' ),
			'financial'     => $this->page_url( array( 'documente-si-informatii-financiare' ), '/ro/documente-si-informatii-financiare/' ),
		);
		return $this->routes_cache['ro'];
	}

	private function page_url( array $slugs, string $fallback, string $language = 'ro' ): string {
		foreach ( $slugs as $slug ) {
			$pages = get_posts(
				array(
					'name'             => $slug,
					'post_type'        => 'page',
					'post_status'      => 'publish',
					'posts_per_page'   => -1,
					'suppress_filters' => false,
					'lang'             => $language,
				)
			);
			foreach ( $pages as $page ) {
				$page_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
				$permalink = get_permalink( $page );
				if ( $language === $page_language || ( ! $page_language && str_contains( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' . $language . '/' ) ) ) {
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

	private function media_item_from_id( int $attachment_id, string $origin ): ?array {
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return null;
		}
		$url = (string) wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! $url ) {
			return null;
		}
		return array(
			'image'   => array( 'id' => $attachment_id, 'url' => $url ),
			'caption' => wp_get_attachment_caption( $attachment_id ) ?: get_the_title( $attachment_id ),
			'_origin' => $origin,
		);
	}

	private function media_item_from_url( string $url, string $origin ): ?array {
		$url = esc_url_raw( html_entity_decode( trim( $url, " \t\n\r\0\x0B\\\"'()" ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! $url || ! preg_match( '/\.(?:avif|gif|jpe?g|png|svg|webp)(?:\?.*)?$/i', $url ) ) {
			return null;
		}
		$attachment_id = (int) attachment_url_to_postid( $url );
		$item = $attachment_id ? $this->media_item_from_id( $attachment_id, $origin ) : null;
		if ( $item ) {
			return $item;
		}
		return array(
			'image'   => array( 'id' => '', 'url' => $url ),
			'caption' => '',
			'_origin' => $origin,
		);
	}

	private function shortcode_media_ids( string $attributes ): array {
		$ids = array();
		if ( preg_match_all( '/(?:^|\s)(?:attach_images|background_image|bg_image|image|images|ids)\s*=\s*(["\'])(.*?)\1/is', $attributes, $matches ) ) {
			foreach ( $matches[2] as $value ) {
				if ( preg_match_all( '/\d+/', $value, $numbers ) ) {
					foreach ( $numbers[0] as $number ) {
						$ids[] = (int) $number;
					}
				}
			}
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function legacy_media_ids( string $content ): array {
		$ids = $this->shortcode_media_ids( $content );
		if ( preg_match_all( '/\bwp-image-(\d+)\b/i', $content, $matches ) ) {
			$ids = array_merge( $ids, array_map( 'intval', $matches[1] ) );
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	private function is_background_media_id( string $content, int $attachment_id ): bool {
		return (bool) preg_match( '/(?:background_image|bg_image)\s*=\s*(["\'])[^"\']*\b' . $attachment_id . '\b[^"\']*\1/i', $content );
	}

	private function legacy_media_urls( string $content ): array {
		$content = str_replace( '\\/', '/', html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! preg_match_all( '#https?://[^\s"\'<>\\)]+/wp-content/uploads/[^\s"\'<>\\)]+#i', $content, $matches ) ) {
			return array();
		}
		return array_values( array_unique( $matches[0] ) );
	}

	private function slider_aliases( string $content ): array {
		$aliases = array();
		if ( preg_match_all( '/\[rev_slider\b([^\]]*)\]/i', $content, $matches ) ) {
			foreach ( $matches[1] as $attributes ) {
				if ( preg_match( '/\balias\s*=\s*(["\'])([^"\']+)\1/i', $attributes, $alias ) ) {
					$aliases[] = sanitize_key( $alias[2] );
				} elseif ( preg_match( '/^\s+([a-z0-9_-]+)/i', $attributes, $alias ) ) {
					$aliases[] = sanitize_key( $alias[1] );
				}
			}
		}
		return array_values( array_unique( array_filter( $aliases ) ) );
	}

	private function slider_media_items( string $content ): array {
		$aliases = $this->slider_aliases( $content );
		if ( ! $aliases ) {
			return array();
		}
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return array();
		}
		$sliders_table = $wpdb->prefix . 'revslider_sliders';
		$slides_table = $wpdb->prefix . 'revslider_slides';
		$items = array();
		foreach ( $aliases as $alias ) {
			// Slider Revolution stores legacy v5 module and slide media in these two tables.
			$slider = $wpdb->get_row( $wpdb->prepare( "SELECT id, params FROM {$sliders_table} WHERE alias = %s LIMIT 1", $alias ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! is_array( $slider ) ) {
				continue;
			}
			$payloads = array( (string) ( $slider['params'] ?? '' ) );
			$slides = $wpdb->get_results( $wpdb->prepare( "SELECT params, layers FROM {$slides_table} WHERE slider_id = %d ORDER BY slide_order ASC", (int) $slider['id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( (array) $slides as $slide ) {
				$payloads[] = (string) ( $slide['params'] ?? '' );
				$payloads[] = (string) ( $slide['layers'] ?? '' );
			}
			foreach ( $this->legacy_media_urls( implode( "\n", $payloads ) ) as $url ) {
				$item = $this->media_item_from_url( $url, 'slider' );
				if ( $item ) {
					$items[] = $item;
				}
			}
		}
		return $items;
	}

	private function legacy_media_items( \WP_Post $page, string $content ): array {
		$items = array();
		$seen = array();
		$add = static function ( ?array $item ) use ( &$items, &$seen ): void {
			if ( ! $item ) {
				return;
			}
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			$key = $id ? 'id:' . $id : 'url:' . strtolower( strtok( $url, '?' ) ?: $url );
			if ( isset( $seen[ $key ] ) ) {
				return;
			}
			$seen[ $key ] = true;
			$items[] = $item;
		};

		$featured_id = (int) get_post_thumbnail_id( $page->ID );
		$add( $this->media_item_from_id( $featured_id, 'featured' ) );
		foreach ( $this->slider_media_items( $content ) as $item ) {
			$add( $item );
		}
		foreach ( $this->legacy_media_ids( $content ) as $attachment_id ) {
			$origin = str_contains( $content, 'wp-image-' . $attachment_id ) ? 'inline' : ( $this->is_background_media_id( $content, $attachment_id ) ? 'background' : 'shortcode' );
			$add( $this->media_item_from_id( $attachment_id, $origin ) );
		}
		foreach ( $this->legacy_media_urls( $content ) as $url ) {
			$origin = preg_match( '/<img\b[^>]*(?:src|data-src)=["\'][^"\']*' . preg_quote( basename( strtok( $url, '?' ) ?: $url ), '/' ) . '/i', $content ) ? 'inline' : 'source-url';
			$add( $this->media_item_from_url( $url, $origin ) );
		}
		foreach ( get_attached_media( 'image', $page->ID ) as $attachment ) {
			$add( $attachment instanceof \WP_Post ? $this->media_item_from_id( $attachment->ID, 'attached' ) : null );
		}
		return $items;
	}

	private function public_media_items( array $items ): array {
		return array_map(
			static fn( array $item ): array => array( 'image' => $item['image'], 'caption' => $item['caption'] ?? '' ),
			$items
		);
	}

	private function primary_media( array $items ): array {
		foreach ( $items as $item ) {
			if ( in_array( $item['_origin'] ?? '', array( 'background', 'featured', 'slider', 'source-url' ), true ) ) {
				return $item['image'];
			}
		}
		return $this->media();
	}

	private function unplaced_media_items( array $items, string $normalized_content, array $primary ): array {
		$unused = array();
		foreach ( $items as $item ) {
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			if ( ( $id && $id === (int) ( $primary['id'] ?? 0 ) ) || ( $url && $url === (string) ( $primary['url'] ?? '' ) ) ) {
				continue;
			}
			if ( ( $id && str_contains( $normalized_content, 'wp-image-' . $id ) ) || ( $url && str_contains( $normalized_content, basename( strtok( $url, '?' ) ?: $url ) ) ) ) {
				continue;
			}
			$unused[] = $item;
		}
		return $this->public_media_items( $unused );
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

	private function original_page_content( \WP_Post $page ): string {
		$stored = get_post_meta( $page->ID, self::SOURCE_META, true );
		if ( is_string( $stored ) && '' !== trim( $stored ) ) {
			return $stored;
		}
		if ( '' !== trim( (string) $page->post_content ) ) {
			return $page->post_content;
		}
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			$rendered = (string) \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page->ID, true );
			if ( '' !== trim( $rendered ) ) {
				update_post_meta( $page->ID, self::SOURCE_META, wp_slash( $rendered ) );
				return $rendered;
			}
		}
		$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
		return 'hu' === $language ? '<p>Az oldal tartalmának frissítése folyamatban van.</p>' : '<p>Conținutul acestei pagini este în curs de actualizare.</p>';
	}

	private function legacy_image_markup( array $ids, bool $gallery = false ): string {
		if ( ! function_exists( 'wp_get_attachment_image' ) ) {
			return '';
		}
		$images = '';
		foreach ( array_unique( array_filter( array_map( 'intval', $ids ) ) ) as $attachment_id ) {
			$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );
			if ( ! $image ) {
				continue;
			}
			$caption = wp_get_attachment_caption( $attachment_id );
			$images .= '<figure class="agris-legacy-media">' . $image . ( $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '' ) . '</figure>';
		}
		return $gallery && $images ? '<div class="agris-legacy-gallery">' . $images . '</div>' : $images;
	}

	private function expand_legacy_media_shortcodes( string $content ): string {
		$content = preg_replace_callback(
			'/\[vc_single_image\b([^\]]*)\](?:\[\/vc_single_image\])?/i',
			fn( array $matches ): string => $this->legacy_image_markup( $this->shortcode_media_ids( $matches[1] ) ),
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:vc_gallery|gallery)\b([^\]]*)\](?:\[\/(?:vc_gallery|gallery)\])?/i',
			fn( array $matches ): string => $this->legacy_image_markup( $this->shortcode_media_ids( $matches[1] ), true ),
			$content
		) ?? $content;
		return $content;
	}

	private function legacy_shortcode_attributes( string $attributes ): array {
		$parsed = array();
		if ( preg_match_all( '/([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s\]]+))/', $attributes, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$parsed[ strtolower( $match[1] ) ] = html_entity_decode( $match[2] ?: ( $match[3] ?: $match[4] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			}
		}
		return $parsed;
	}

	private function expand_legacy_link_shortcodes( string $content ): string {
		$content = preg_replace_callback(
			'/\[(?:otw_shortcode_button|otw-button)\b([^\]]*)\](.*?)\[\/(?:otw_shortcode_button|otw-button)\]/is',
			function ( array $matches ): string {
				$attributes = $this->legacy_shortcode_attributes( $matches[1] );
				$url = (string) ( $attributes['href'] ?? $attributes['link'] ?? '' );
				return $url ? '<a class="agris-legacy-button" href="' . esc_url( $url ) . '">' . wp_kses_post( $matches[2] ) . '</a>' : $matches[2];
			},
			$content
		) ?? $content;
		$content = preg_replace_callback(
			'/\[(?:button|qode_button)\b([^\]]*)\](?:\[\/(?:button|qode_button)\])?/i',
			function ( array $matches ): string {
				$attributes = $this->legacy_shortcode_attributes( $matches[1] );
				$url = (string) ( $attributes['link'] ?? $attributes['href'] ?? '' );
				$text = (string) ( $attributes['text'] ?? $attributes['title'] ?? $url );
				return $url ? '<a class="agris-legacy-button" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>' : esc_html( $text );
			},
			$content
		) ?? $content;
		return $content;
	}

	private function normalize_legacy_content( string $content ): string {
		if ( str_contains( $content, 'agris-header-wrap' ) || str_contains( $content, 'elementor-widget-agris-site-header' ) ) {
			$content = $this->extract_nested_richtext( $content );
		}
		$content = $this->expand_legacy_link_shortcodes( $content );
		$content = $this->expand_legacy_media_shortcodes( $content );
		$protected_media = array();
		$content = preg_replace_callback(
			'/\[\/?(?:audio|caption|embed|playlist|video)\b[^\]]*\]/i',
			static function ( array $matches ) use ( &$protected_media ): string {
				$key = '<!--agris-protected-media-' . count( $protected_media ) . '-->';
				$protected_media[ $key ] = $matches[0];
				return $key;
			},
			$content
		) ?? $content;
		$content = preg_replace( '/\[(?:\/?)[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]/u', '', $content ) ?? $content;
		$content = strtr( $content, $protected_media );
		$content = preg_replace( '/<h1(\s[^>]*)?>/i', '<h2$1>', $content ) ?? $content;
		$content = preg_replace( '/<\/h1>/i', '</h2>', $content ) ?? $content;
		$content = preg_replace( '/<p>\s*(?:&nbsp;)?\s*<\/p>/i', '', $content ) ?? $content;
		$content = trim( $content );
		$has_media = (bool) preg_match( '/<(?:audio|figure|iframe|img|video)\b/i', $content );
		return '' !== wp_strip_all_tags( $content ) || $has_media ? $content : '<p>Conținutul acestei pagini este în curs de actualizare.</p>';
	}

	private function extract_nested_richtext( string $content ): string {
		if ( ! class_exists( '\DOMDocument' ) || ! class_exists( '\DOMXPath' ) ) {
			return '';
		}

		$previous_errors = libxml_use_internal_errors( true );
		$document = new \DOMDocument();
		$loaded = $document->loadHTML( '<?xml encoding="utf-8" ?><div id="agris-source-root">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );
		if ( ! $loaded ) {
			return '';
		}

		$xpath = new \DOMXPath( $document );
		$nodes = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " agris-richtext ") and not(.//*[contains(concat(" ", normalize-space(@class), " "), " agris-richtext ")])]' );
		if ( ! $nodes || 0 === $nodes->length ) {
			return '';
		}

		$node = $nodes->item( $nodes->length - 1 );
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $document->saveHTML( $child );
		}
		return $html;
	}

	private function legacy_post_queries( string $content ): array {
		if ( ! preg_match_all( '/\[(masonry_blog|latest_post)\b([^\]]*)\]/i', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$queries = array();
		foreach ( $matches as $match ) {
			$attributes = $this->legacy_shortcode_attributes( $match[2] );
			$category = trim( (string) ( $attributes['category'] ?? '' ) );
			if ( ! $category ) {
				continue;
			}
			$categories = array_filter( array_map( 'sanitize_title', preg_split( '/\s*,\s*/', $category ) ?: array() ) );
			$count = (int) ( $attributes['number_of_posts'] ?? 0 );
			if ( 'latest_post' === strtolower( $match[1] ) ) {
				$columns = max( 1, (int) ( $attributes['number_of_colums'] ?? $attributes['number_of_columns'] ?? 3 ) );
				$rows = max( 1, (int) ( $attributes['number_of_rows'] ?? 1 ) );
				$count = $columns * $rows;
			}
			$queries[] = array(
				'category'     => implode( ',', $categories ),
				'count'        => $count ?: 6,
				'columns'      => min( 4, max( 2, (int) ( $attributes['number_of_colums'] ?? $attributes['number_of_columns'] ?? 3 ) ) ),
				'orderby'      => strtolower( (string) ( $attributes['order_by'] ?? 'date' ) ),
				'show_excerpt' => 'yes',
			);
		}
		return $queries;
	}

	private function legacy_category_slug( string $content ): string {
		$queries = $this->legacy_post_queries( $content );
		return (string) ( $queries[0]['category'] ?? '' );
	}

	private function gallery_items( \WP_Post $page, string $category_slug, string $source_content = '' ): array {
		$items = $this->legacy_media_items( $page, $source_content );
		$seen = array();
		foreach ( $items as $item ) {
			$id = (int) ( $item['image']['id'] ?? 0 );
			$url = (string) ( $item['image']['url'] ?? '' );
			$seen[ $id ? 'id:' . $id : 'url:' . strtolower( $url ) ] = true;
		}
		if ( $category_slug ) {
			$category = get_category_by_slug( $category_slug );
			if ( $category ) {
				foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 100, 'category' => $category->term_id ) ) as $post ) {
					$thumbnail_id = (int) get_post_thumbnail_id( $post->ID );
					if ( $thumbnail_id ) {
						$item = $this->media_item_from_id( $thumbnail_id, 'category' );
						if ( $item && ! isset( $seen[ 'id:' . $thumbnail_id ] ) ) {
							$seen[ 'id:' . $thumbnail_id ] = true;
							$items[] = $item;
						}
						continue;
					}
					foreach ( get_attached_media( 'image', $post->ID ) as $attachment ) {
						$item = $attachment instanceof \WP_Post ? $this->media_item_from_id( $attachment->ID, 'category' ) : null;
						if ( $item && ! isset( $seen[ 'id:' . $attachment->ID ] ) ) {
							$seen[ 'id:' . $attachment->ID ] = true;
							$items[] = $item;
						}
					}
				}
			}
		}
		return $this->public_media_items( $items );
	}

	private function recent_updates( string $language, int $count = 3 ): array {
		$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => max( 1, $count * 3 ), 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false, 'lang' => $language ) );
		$items = array();
		foreach ( $posts as $post ) {
			$post_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID, 'slug' ) : '';
			$path = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
			if ( $post_language && $language !== $post_language ) {
				continue;
			}
			if ( ! $post_language && ! str_contains( $path, '/' . $language . '/' ) ) {
				continue;
			}
			$items[] = array( 'day' => get_the_date( 'd', $post ), 'title' => get_the_title( $post ), 'meta' => get_the_date( 'Y. m. d.', $post ), 'url' => $this->link( get_permalink( $post ) ) );
			if ( count( $items ) >= $count ) {
				break;
			}
		}
		return $this->repeater( 'updates-' . $language, $items );
	}

	private function interface_copy( string $language ): array {
		if ( 'hu' === $language ) {
			return array(
				'official' => 'Egri Község Polgármesteri Hivatalának hivatalos oldala, Szatmár megye, Románia', 'trust' => 'Biztonságos kapcsolat',
				'brand_subtitle' => 'Egri Község Polgármesteri Hivatala', 'cta' => 'Helyi hivatalos közlöny', 'home' => 'Kezdőlap',
				'skip' => 'Ugrás a tartalomhoz', 'language' => 'Nyelvválasztás', 'nav' => 'Fő navigáció', 'search' => 'Keresés',
				'menu_open' => 'Menü megnyitása', 'menu_close' => 'Menü bezárása', 'submenu' => '%s almenüjének megnyitása',
				'search_title' => 'Keresés a portálon', 'search_placeholder' => 'Dokumentumok, felhívások és szolgáltatások keresése…', 'search_button' => 'Keresés', 'close' => 'Bezárás',
				'footer_description' => 'Hivatalos portál ügyintézéshez, közérdekű dokumentumokhoz és önkormányzati tájékoztatáshoz.',
				'office' => 'Polgármesteri Hivatal', 'leadership' => 'Vezetőség', 'council' => 'Helyi tanács', 'public' => 'Közérdekű', 'announcements' => 'Felhívások', 'monitor' => 'Helyi hivatalos közlöny',
				'contact' => 'Elérhetőség', 'footer_nav' => 'Lábléc hivatkozások', 'back_top' => 'Vissza az oldal tetejére', 'copyright' => 'Minden jog fenntartva, Egri Község.',
				'address' => 'Románia, 447066, Egri, Csűry Bálint utca 68., Szatmár megye',
				'accessibility' => 'Akadálymentesítés', 'text_size' => 'Szövegméret', 'contrast' => 'Nagy kontraszt', 'grayscale' => 'Szürkeárnyalat', 'underline' => 'Hivatkozások aláhúzása', 'reset' => 'Beállítások visszaállítása', 'options' => 'Akadálymentesítési beállítások',
				'empty_posts' => 'Ehhez a válogatáshoz jelenleg nincs közzétett bejegyzés.', 'read_more' => 'Tovább →', 'all' => 'Összes',
			);
		}
		return array(
			'official' => 'Site oficial al Primăriei Comunei Agriș, județul Satu Mare, România', 'trust' => 'Conexiune securizată',
			'brand_subtitle' => 'Primăria Comunei Agriș', 'cta' => 'Monitorul Oficial', 'home' => 'Acasă',
			'skip' => 'Sari la conținut', 'language' => 'Alege limba', 'nav' => 'Navigație principală', 'search' => 'Caută',
			'menu_open' => 'Deschide meniul', 'menu_close' => 'Închide meniul', 'submenu' => 'Deschide submeniul pentru %s',
			'search_title' => 'Căutare în portal', 'search_placeholder' => 'Căutați documente, anunțuri, servicii…', 'search_button' => 'Caută', 'close' => 'Închide',
			'footer_description' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.',
			'office' => 'Primăria', 'leadership' => 'Conducere', 'council' => 'Consiliul Local', 'public' => 'Informații publice', 'announcements' => 'Anunțuri', 'monitor' => 'Monitorul Oficial',
			'contact' => 'Contact', 'footer_nav' => 'Linkuri subsol', 'back_top' => 'Înapoi sus', 'copyright' => 'Toate drepturile rezervate Comuna Agriș.',
			'address' => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare',
			'accessibility' => 'Accesibilitate', 'text_size' => 'Mărime text', 'contrast' => 'Contrast ridicat', 'grayscale' => 'Tonuri de gri', 'underline' => 'Linkuri subliniate', 'reset' => 'Resetează setările', 'options' => 'Opțiuni de accesibilitate',
			'empty_posts' => 'Nu există articole publicate pentru această selecție.', 'read_more' => 'Citește mai mult →', 'all' => 'Toate',
		);
	}

	private function header_settings( \WP_Post $page, string $language, array $routes, string $seed ): array {
		$copy = $this->interface_copy( $language );
		$other_language = 'hu' === $language ? 'ro' : 'hu';
		$other_url = $this->translated_url( $page->ID, $other_language, $routes[ 'home_' . $other_language ] );
		$language_items = 'hu' === $language
			? array( array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( get_permalink( $page ) ) ), array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( $other_url ) ) )
			: array( array( 'code' => 'RO', 'label' => 'Română', 'url' => $this->link( get_permalink( $page ) ) ), array( 'code' => 'HU', 'label' => 'Magyar', 'url' => $this->link( $other_url ) ) );
		return array(
			'official_text' => $copy['official'], 'trust_text' => $copy['trust'], 'mail_url' => $this->link( 'mailto:primaria@comunaagris.ro' ), 'logo' => $this->media(),
			'brand_title' => 'hu' === $language ? 'Egri Község' : 'Comuna Agriș', 'brand_subtitle' => $copy['brand_subtitle'], 'home_url' => $this->link( $routes[ 'home_' . $language ] ), 'menu_id' => $this->menu_id( $language ),
			'cta_text' => $copy['cta'], 'cta_link' => $this->link( $routes['monitor'] ), 'agris_sticky' => 'yes', 'language_items' => $this->repeater( $seed . '-languages', $language_items ),
			'skip_label' => $copy['skip'], 'language_label' => $copy['language'], 'nav_label' => $copy['nav'], 'search_label' => $copy['search'],
			'menu_open_label' => $copy['menu_open'], 'menu_close_label' => $copy['menu_close'], 'submenu_label' => $copy['submenu'],
		);
	}

	private function search_settings( string $language ): array {
		$copy = $this->interface_copy( $language );
		return array( 'title' => $copy['search_title'], 'placeholder' => $copy['search_placeholder'], 'button_text' => $copy['search_button'], 'close_label' => $copy['close'], 'language' => $language, 'modal' => 'yes' );
	}

	private function footer_settings( string $language, array $routes, string $seed ): array {
		$copy = $this->interface_copy( $language );
		return array(
			'title' => 'hu' === $language ? 'Egri Község' : 'Comuna Agriș', 'subtitle' => $copy['brand_subtitle'], 'description' => $copy['footer_description'],
			'links' => $this->repeater( $seed . '-links', array(
				array( 'column' => $copy['office'], 'label' => $copy['leadership'], 'url' => $this->link( $routes['mayor'] ) ),
				array( 'column' => $copy['office'], 'label' => $copy['council'], 'url' => $this->link( $routes['council'] ) ),
				array( 'column' => $copy['public'], 'label' => $copy['announcements'], 'url' => $this->link( $routes['announcements'] ) ),
				array( 'column' => $copy['public'], 'label' => $copy['monitor'], 'url' => $this->link( $routes['monitor'] ) ),
			) ),
			'phone' => '0261 878 112', 'email' => 'primaria@comunaagris.ro', 'address' => $copy['address'], 'copyright' => $copy['copyright'],
			'contact_url' => $this->link( $routes['contact'] ), 'monitor_url' => $this->link( $routes['monitor'] ), 'contact_title' => $copy['contact'],
			'contact_link_text' => $copy['contact'], 'monitor_link_text' => $copy['monitor'], 'footer_nav_label' => $copy['footer_nav'], 'back_to_top_label' => $copy['back_top'],
		);
	}

	private function accessibility_settings( string $language ): array {
		$copy = $this->interface_copy( $language );
		return array( 'title' => $copy['accessibility'], 'position' => 'right', 'text_size_label' => $copy['text_size'], 'contrast_label' => $copy['contrast'], 'grayscale_label' => $copy['grayscale'], 'underline_label' => $copy['underline'], 'reset_label' => $copy['reset'], 'options_label' => $copy['options'], 'back_to_top_label' => $copy['back_top'] );
	}

	private function generic_page_data( \WP_Post $page, string $type, string $language = 'ro' ): array {
		$routes = $this->routes( $language );
		$copy = $this->interface_copy( $language );
		$seed = 'page-' . $page->ID;
		$labels = 'hu' === $language ? array(
			'administration' => array( 'Polgármesteri Hivatal', 'A helyi közigazgatás, a vezetőség és az ügyfélkapcsolatok információi.' ),
			'documents'      => array( 'Közérdekű adatok', 'Egri Község dokumentumai, felhívásai és nyilvános információi.' ),
			'community'      => array( 'Egri Község', 'Hasznos információk a községről és a helyi közszolgáltatásokról.' ),
			'galleries'      => array( 'Galéria', 'Képek Egri Község életéből és történetéből.' ),
		) : array(
			'administration' => array( 'Administrație', 'Informații despre administrația locală, conducere și relația cu cetățenii.' ),
			'documents'      => array( 'Transparență', 'Documente, anunțuri și informații publice ale Comunei Agriș.' ),
			'community'      => array( 'Comuna Agriș', 'Informații utile despre comunitate și serviciile publice locale.' ),
			'galleries'      => array( 'Galerie', 'Imagini din viața și istoria Comunei Agriș.' ),
		);
		$label = $labels[ $type ] ?? $labels['community'];
		$excerpt = trim( wp_strip_all_tags( (string) $page->post_excerpt ) );
		$description = $excerpt;
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$image = $this->primary_media( $media_items );
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$unplaced_media = $this->unplaced_media_items( $media_items, $normalized_content, $image );
		$legacy_queries = $this->legacy_post_queries( $source_content );

		$data = array(
			$this->container(
				$seed . '-header',
				array(
					$this->widget( $seed . '-header-widget', 'agris-site-header', $this->header_settings( $page, $language, $routes, $seed . '-header' ) ),
					$this->widget( $seed . '-search', 'agris-search-box', $this->search_settings( $language ) ),
				),
				array( 'content_width' => 'full' )
			),
			$this->container(
				$seed . '-hero',
				array( $this->widget( $seed . '-hero-widget', 'agris-page-hero', array(
					'kicker' => $label[0],
					'title' => get_the_title( $page ),
					'description' => $description,
					'parent_label' => $copy['home'],
					'parent_link' => $this->link( $routes[ 'home_' . $language ] ),
					'current_label' => get_the_title( $page ),
					'background' => $image,
				) ) ),
				array( 'content_width' => 'full' )
			),
			$this->container(
				$seed . '-content',
				array( $this->widget( $seed . '-content-widget', 'agris-content-media', array(
					'kicker' => '',
					'title' => '',
					'description' => '',
					'content' => $normalized_content,
					'image' => array( 'id' => 0, 'url' => '' ),
					'image_side' => 'right',
				) ) ),
				array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) )
			),
		);

		if ( 'galleries' === $type ) {
			$gallery_items = $this->gallery_items( $page, '', $source_content );
			if ( $gallery_items ) {
				$data[] = $this->container( $seed . '-gallery', array( $this->widget( $seed . '-gallery-widget', 'agris-photo-gallery', array( 'kicker' => 'hu' === $language ? 'Galéria' : 'Galerie', 'title' => get_the_title( $page ), 'description' => $description, 'items_list' => $this->repeater( $seed . '-images', $gallery_items ), 'columns' => '3' ) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
			}
		}
		foreach ( $legacy_queries as $query_index => $query ) {
			$data[] = $this->container( $seed . '-legacy-posts-' . $query_index, array( $this->widget( $seed . '-legacy-posts-widget-' . $query_index, 'agris-news-grid', array(
				'post_type' => 'post', 'category' => $query['category'], 'language' => $language, 'count' => $query['count'], 'columns' => $query['columns'], 'orderby' => $query['orderby'], 'show_excerpt' => $query['show_excerpt'], 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'],
			) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		}
		if ( 'galleries' !== $type && $unplaced_media ) {
			$data[] = $this->container( $seed . '-media', array( $this->widget( $seed . '-media-widget', 'agris-photo-gallery', array( 'kicker' => 'hu' === $language ? 'Média' : 'Media', 'title' => 'hu' === $language ? 'Képek és médiatartalmak' : 'Imagini și materiale media', 'description' => $description, 'items_list' => $this->repeater( $seed . '-media-items', $unplaced_media ), 'columns' => count( $unplaced_media ) > 2 ? '3' : '2' ) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) );
		}
		$data[] = $this->container( $seed . '-footer', array(
			$this->widget( $seed . '-footer-widget', 'agris-site-footer', $this->footer_settings( $language, $routes, $seed . '-footer' ) ),
			$this->widget( $seed . '-accessibility', 'agris-accessibility', $this->accessibility_settings( $language ) ),
		), array( 'content_width' => 'full' ) );

		return $data;
	}

	private function home_ro_data( \WP_Post $page ): array {
		$menu_id = $this->menu_id();
		$routes  = $this->routes();
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$uploads = wp_get_upload_dir();
		$hero_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/hatter-slider-46.jpg', 'slider' ) : null;
		$hero_background = $hero_item ? $hero_item['image'] : $this->primary_media( $media_items );
		$cta_item = $this->media_item_from_id( 4295, 'shortcode' );
		$cta_image = $cta_item ? $cta_item['image'] : $this->media();
		$community_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2019/02/egriii.jpg', 'homepage-community' ) : null;
		$community_image = $community_item ? $community_item['image'] : $this->media();
		$community_content = '<p>Noua structură păstrează conținutul esențial din site-ul vechi: istoria comunei, localizarea, monumentele istorice, turismul, sportul și legăturile cu localitățile înfrățite.</p>'
			. '<div class="agris-home-link-list">'
			. '<a href="' . esc_url( $routes['history'] ) . '">Istoria comunei <span>→</span></a>'
			. '<a href="' . esc_url( $routes['monuments'] ) . '">Monumente istorice <span>→</span></a>'
			. '<a href="' . esc_url( $routes['tourism'] ) . '">Ecoturism și agroturism <span>→</span></a>'
			. '<a href="' . esc_url( $routes['twinned'] ) . '">Comune înfrățite <span>→</span></a>'
			. '</div>';

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
							'agris_sticky'   => 'yes',
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
							'eyebrow'        => 'Ghișeul este deschis · Luni–Vineri 8:00–16:00',
							'title'          => 'Servicii publice transparente pentru Comuna Agriș.',
							'description'    => 'Portal modern pentru documente, formulare, hotărâri, anunțuri oficiale și informații utile pentru cetățeni.',
							'primary_text'   => 'Vezi documentele',
							'primary_link'   => $this->link( $routes['public_info'] ),
							'secondary_text' => 'Contact rapid',
							'secondary_link' => $this->link( $routes['contact'] ),
							'background'     => $hero_background,
							'show_search'    => 'yes',
							'updates_title'  => 'Noutăți din portal',
							'updates_items'  => $this->repeater( 'home-updates', array(
								array( 'day' => '08', 'title' => 'ANUNȚ INDIVIDUAL', 'meta' => 'Publicat în iulie 2026', 'url' => $this->link( home_url( '/ro/anunt-idividual/' ) ) ),
								array( 'day' => '18', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => 'Hotărâri publicate pe 18 mai 2026', 'url' => $this->link( home_url( '/ro/hotararea-consiliului-local-nr-13-16-2026-2/' ) ) ),
								array( 'day' => '24', 'title' => 'H.C.L. nr. 4–9 / 2026', 'meta' => 'Arhivă Consiliul Local', 'url' => $this->link( home_url( '/ro/hotararea-consiliului-local-nr-4-9-2026/' ) ) ),
							) ),
						)
					),
				),
				array( 'content_width' => 'full' )
			),
			$this->section( 'services-head', 'Acces rapid', 'Servicii frecvente', '', 'Toate informațiile', $routes['public_info'], 'light', '#ffffff' ),
			$this->container(
				'services',
				array(
					$this->widget(
						'services-widget',
						'agris-services-grid',
						array(
							'columns'    => '4',
							'items_list' => $this->repeater( 'services', array(
								array( 'icon' => 'TVA', 'title' => 'Taxe și impozite', 'description' => 'Informații pentru contribuabili și plăți locale.', 'url' => $this->link( $routes['taxes'] ) ),
								array( 'icon' => 'PDF', 'title' => 'Formulare tipizate', 'description' => 'Cereri și documente administrative utile.', 'url' => $this->link( $routes['forms'] ) ),
								array( 'icon' => 'URB', 'title' => 'Urbanism', 'description' => 'Certificate de urbanism și autorizații de construire.', 'url' => $this->link( $routes['urbanism'] ) ),
								array( 'icon' => 'AGR', 'title' => 'Registru agricol', 'description' => 'Date și servicii pentru gospodării și terenuri.', 'url' => $this->link( $routes['agricultural'] ) ),
								array( 'icon' => 'SC', 'title' => 'Stare civilă', 'description' => 'Acte, certificate și proceduri de stare civilă.', 'url' => $this->link( $this->page_url( array( 'stare-civila' ), '/ro/stare-civila/' ) ) ),
								array( 'icon' => 'AS', 'title' => 'Asistență socială', 'description' => 'Sprijin și programe pentru comunitate.', 'url' => $this->link( $this->page_url( array( 'asistenta-sociala' ), '/ro/asistenta-sociala/' ) ) ),
								array( 'icon' => 'L17', 'title' => 'Legea 17', 'description' => 'Oferte de vânzare și legislație aferentă.', 'url' => $this->link( home_url( '/ro/category/legea17/' ) ) ),
								array( 'icon' => 'APIA', 'title' => 'APIA', 'description' => 'Informații agricole și comunicări publice.', 'url' => $this->link( home_url( '/ro/category/apia/' ) ) ),
							) ),
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#ffffff',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'news-head', 'Actualizat după site-ul original', 'Anunțuri oficiale', '', 'Toate anunțurile', $routes['announcements'] ),
			$this->container(
				'news',
				array(
					$this->widget(
						'news-widget',
						'agris-news-grid',
						array(
							'post_type'    => 'post',
							'category'     => 'anunturi',
							'count'        => 3,
							'columns'      => '3',
							'orderby'      => 'date',
							'show_excerpt' => 'yes',
							'show_category'=> 'yes',
							'show_date'    => 'yes',
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'decisions-head', 'Consiliul Local', 'Hotărâri recente', '', 'Arhiva H.C.L.', $routes['decisions'], 'dark', '#0f2f1f' ),
			$this->container(
				'decisions',
				array(
					$this->widget(
						'decisions-widget',
						'agris-document-grid',
						array(
							'columns'    => '3',
							'filters'    => '',
							'items_list' => $this->repeater( 'home-decisions', array(
								array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => '18 mai 2026', 'category' => 'Hotărâri', 'url' => $this->link( home_url( '/ro/hotararea-consiliului-local-nr-13-16-2026-2/' ) ) ),
								array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 10–12 / 2026', 'meta' => '18 mai 2026', 'category' => 'Hotărâri', 'url' => $this->link( home_url( '/ro/hotararea-consiliului-local-nr-10-12-2026/' ) ) ),
								array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 4–9 / 2026', 'meta' => '24 martie 2026', 'category' => 'Hotărâri', 'url' => $this->link( home_url( '/ro/hotararea-consiliului-local-nr-4-9-2026/' ) ) ),
							) ),
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#0f2f1f',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'community',
				array(
					$this->widget( 'community-widget', 'agris-content-media', array(
						'kicker' => 'Comuna',
						'title' => 'Istorie, cultură și natură în inima județului Satu Mare',
						'description' => '',
						'content' => $community_content,
						'image' => $community_image,
						'image_side' => 'right',
					) ),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->section( 'monitor-head', 'Monitorul Oficial Local', 'Documente publice într-un singur loc', '', 'Deschide MOL', $routes['monitor'], 'light', '#ffffff' ),
			$this->container(
				'monitor-services',
				array(
					$this->widget( 'monitor-services-widget', 'agris-services-grid', array(
						'columns' => '3',
						'items_list' => $this->repeater( 'monitor-services', array(
							array( 'icon' => 'CL', 'title' => 'Hotărârile autorității deliberative', 'description' => 'Arhivă pentru hotărârile Consiliului Local.', 'url' => $this->link( $routes['deliberative'] ) ),
							array( 'icon' => 'PR', 'title' => 'Dispoziții autoritatea executivă', 'description' => 'Documente emise de conducerea executivă.', 'url' => $this->link( $routes['executive'] ) ),
							array( 'icon' => 'FIN', 'title' => 'Documente și informații financiare', 'description' => 'Buget, execuție și date financiare publice.', 'url' => $this->link( $routes['financial'] ) ),
						) ),
					) ),
				),
				array(
					'background_background' => 'classic',
					'background_color' => '#ffffff',
					'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ),
				)
			),
			$this->container(
				'cta',
				array(
					$this->widget(
						'cta-widget',
						'agris-cta-banner',
						array(
							'kicker'      => 'Transparență administrativă',
							'title'       => 'Guvernare transparentă, deschisă și participativă',
							'description' => 'Bannerul SIPOCĂ din site-ul original a fost păstrat ca reper instituțional.',
							'image'       => $cta_image,
							'button_text' => '',
							'button_link' => $this->link(),
						)
					),
				),
				array(
					'padding' => array( 'unit' => 'px', 'top' => '48', 'right' => '0', 'bottom' => '48', 'left' => '0', 'isLinked' => false ),
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
							'contact_url' => $this->link( $routes['contact'] ),
							'monitor_url' => $this->link( $routes['monitor'] ),
						)
					),
					$this->widget( 'accessibility-widget', 'agris-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				),
				array( 'content_width' => 'full' )
			),
		);
	}

	private function home_hu_data( \WP_Post $page ): array {
		$routes = $this->routes( 'hu' );
		$copy = $this->interface_copy( 'hu' );
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$uploads = wp_get_upload_dir();
		$hero_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2018/07/hatter-slider-46.jpg', 'slider' ) : null;
		$hero_background = $hero_item ? $hero_item['image'] : $this->primary_media( $media_items );
		$community_item = ! empty( $uploads['baseurl'] ) ? $this->media_item_from_url( trailingslashit( $uploads['baseurl'] ) . '2019/02/egriii.jpg', 'homepage-community' ) : null;
		$community_image = $community_item ? $community_item['image'] : $this->media();
		$community_content = '<p>Ismerje meg Egri Község történetét, földrajzi elhelyezkedését, emlékműveit, természeti értékeit és testvértelepülési kapcsolatait.</p><div class="agris-home-link-list">'
			. '<a href="' . esc_url( $routes['history'] ) . '">Községünk története <span>→</span></a>'
			. '<a href="' . esc_url( $routes['location'] ) . '">A község elhelyezkedése <span>→</span></a>'
			. '<a href="' . esc_url( $routes['monuments'] ) . '">Emlékművek <span>→</span></a>'
			. '<a href="' . esc_url( $routes['tourism'] ) . '">Ökoturizmus <span>→</span></a>'
			. '<a href="' . esc_url( $routes['twinned'] ) . '">Testvértelepülések <span>→</span></a></div>';

		return array(
			$this->container( 'hu-header', array(
				$this->widget( 'hu-header-widget', 'agris-site-header', $this->header_settings( $page, 'hu', $routes, 'hu-home-header' ) ),
				$this->widget( 'hu-search-modal', 'agris-search-box', $this->search_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'hu-hero', array(
				$this->widget( 'hu-hero-widget', 'agris-home-hero', array(
					'eyebrow' => 'Ügyfélfogadás · Hétfő–péntek 8:00–16:00',
					'title' => 'Isten hozta Önöket Egri Község hivatalos honlapján!',
					'description' => 'Közérdekű tájékoztatás, hivatali ügyintézés, felhívások, dokumentumok és közösségi hírek egy helyen.',
					'primary_text' => 'Közérdekű információk', 'primary_link' => $this->link( $routes['public_info'] ),
					'secondary_text' => 'Elérhetőség', 'secondary_link' => $this->link( $routes['contact'] ), 'background' => $hero_background, 'show_search' => 'yes',
					'search_label' => $copy['search'], 'search_placeholder' => $copy['search_placeholder'], 'search_button' => $copy['search_button'], 'search_language' => 'hu',
					'updates_title' => 'Legutóbbi bejegyzések', 'updates_items' => $this->recent_updates( 'hu', 3 ),
				) ),
			), array( 'content_width' => 'full' ) ),
			$this->section( 'hu-services-head', 'Gyors elérés', 'Közérdekű ügyek', '', 'Minden felhívás', $routes['announcements'], 'light', '#ffffff' ),
			$this->container( 'hu-services', array(
				$this->widget( 'hu-services-widget', 'agris-services-grid', array( 'columns' => '4', 'items_list' => $this->repeater( 'hu-services', array(
					array( 'icon' => 'KÖZ', 'title' => 'Helyi hivatalos közlöny', 'description' => 'Határozatok, rendelkezések és nyilvános dokumentumok.', 'url' => $this->link( $routes['monitor'] ) ),
					array( 'icon' => 'PDF', 'title' => 'Formanyomtatványok', 'description' => 'Hivatali kérelmek és letölthető űrlapok.', 'url' => $this->link( $routes['forms'] ) ),
					array( 'icon' => '17', 'title' => '17-es törvény', 'description' => 'Hirdetmények és a kapcsolódó nyilvános információk.', 'url' => $this->link( $routes['law17'] ) ),
					array( 'icon' => 'HT', 'title' => 'Határozattervezetek', 'description' => 'A helyi tanács tervezetei és döntései.', 'url' => $this->link( $routes['decisions'] ) ),
					array( 'icon' => 'EL', 'title' => 'Elérhetőség', 'description' => 'Telefonszámok, cím és ügyfélfogadás.', 'url' => $this->link( $routes['contact'] ) ),
					array( 'icon' => 'HT', 'title' => 'Helyi tanács', 'description' => 'A helyi tanács szerkezete és határozatai.', 'url' => $this->link( $routes['council'] ) ),
					array( 'icon' => 'EG', 'title' => 'Községünk', 'description' => 'Történet, kultúra, sport és turizmus.', 'url' => $this->link( $routes['history'] ) ),
					array( 'icon' => 'FOT', 'title' => 'Galéria', 'description' => 'Képek a község eseményeiről és mindennapjairól.', 'url' => $this->link( $routes['galleries'] ) ),
				) ) ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-community', array(
				$this->widget( 'hu-community-widget', 'agris-content-media', array( 'kicker' => 'Községünk', 'title' => 'Történet, kultúra és természeti értékek Szatmár szívében', 'description' => '', 'content' => $community_content, 'image' => $community_image, 'image_side' => 'right' ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '72', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-announcements-head', 'Közérdekű', 'Felhívások', '', 'Minden felhívás', $routes['announcements'] ),
			$this->container( 'hu-announcements', array(
				$this->widget( 'hu-announcements-widget', 'agris-news-grid', array( 'post_type' => 'post', 'category' => 'felhivasok', 'language' => 'hu', 'count' => 3, 'columns' => '3', 'orderby' => 'date', 'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'] ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-events-head', 'Közösség', 'Események', '', 'Minden esemény', $routes['events'], 'dark', '#0f2f1f' ),
			$this->container( 'hu-events', array(
				$this->widget( 'hu-events-widget', 'agris-news-grid', array( 'post_type' => 'post', 'category' => 'esemenyek', 'language' => 'hu', 'count' => 3, 'columns' => '3', 'orderby' => 'date', 'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'] ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#0f2f1f', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '76', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-journal-head', 'Helyi kiadvány', 'Egri Napló', '', 'Az Egri Napló archívuma', $routes['journal'] ),
			$this->container( 'hu-journal', array(
				$this->widget( 'hu-journal-widget', 'agris-news-grid', array( 'post_type' => 'post', 'category' => 'egri-naplo', 'language' => 'hu', 'count' => 3, 'columns' => '3', 'orderby' => 'date', 'show_excerpt' => 'yes', 'show_category' => 'yes', 'show_date' => 'yes', 'empty_text' => $copy['empty_posts'], 'read_more_text' => $copy['read_more'] ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ) ) ),
			$this->section( 'hu-monitor-head', 'Önkormányzati átláthatóság', 'Helyi hivatalos közlöny', '', 'Közlöny megnyitása', $routes['monitor'], 'light', '#ffffff' ),
			$this->container( 'hu-monitor', array(
				$this->widget( 'hu-monitor-widget', 'agris-services-grid', array( 'columns' => '3', 'items_list' => $this->repeater( 'hu-monitor-items', array(
					array( 'icon' => 'HT', 'title' => 'A tanácsadó hatóság rendelkezései', 'description' => 'A helyi tanács határozatainak archívuma.', 'url' => $this->link( $routes['deliberative'] ) ),
					array( 'icon' => 'VH', 'title' => 'A végrehajtó hatóság rendelkezései', 'description' => 'A végrehajtó vezetőség által kiadott dokumentumok.', 'url' => $this->link( $routes['executive'] ) ),
					array( 'icon' => 'PÉN', 'title' => 'Pénzügyi dokumentumok és információk', 'description' => 'Költségvetési és pénzügyi adatok.', 'url' => $this->link( $routes['financial'] ) ),
				) ) ) ),
			), array( 'background_background' => 'classic', 'background_color' => '#ffffff', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '72', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'hu-footer', array(
				$this->widget( 'hu-footer-widget', 'agris-site-footer', $this->footer_settings( 'hu', $routes, 'hu-home-footer' ) ),
				$this->widget( 'hu-accessibility-widget', 'agris-accessibility', $this->accessibility_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
		);
	}

	private function mayor_ro_data( \WP_Post $page ): array {
		$menu_id = $this->menu_id();
		$routes  = $this->routes();
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$mayor_photo = $media_items ? $media_items[0]['image'] : $this->media();
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
							'agris_sticky'   => 'yes',
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
							'kicker'        => '',
							'title'         => 'Primar',
							'description'   => '',
							'parent_label'  => 'Acasă',
							'parent_link'   => $this->link( $routes['home_ro'] ),
							'current_label' => 'Primar',
							'background'    => $mayor_photo,
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
							'photo'    => $mayor_photo,
							'role'     => 'Primar',
							'name'     => 'Szabo Elek',
							'subtitle' => '',
							'bio'      => '<p><strong>Data nașterii:</strong> 03.10.1963</p>',
							'phone'    => '0261 878 111',
							'email'    => 'primar@comunaagris.ro',
							'office'   => 'Luni 9:00–11:00 · Joi 9:00–11:00',
						)
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '70', 'right' => '0', 'bottom' => '36', 'left' => '0', 'isLinked' => false ) )
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
							'contact_url' => $this->link( $routes['contact'] ),
							'monitor_url' => $this->link( $routes['monitor'] ),
						)
					),
					$this->widget( 'mayor-accessibility-widget', 'agris-accessibility', array( 'title' => 'Accesibilitate', 'position' => 'right' ) ),
				),
				array( 'content_width' => 'full' )
			),
		);
	}

	private function mayor_hu_data( \WP_Post $page ): array {
		$routes = $this->routes( 'hu' );
		$source_content = $this->original_page_content( $page );
		$media_items = $this->legacy_media_items( $page, $source_content );
		$mayor_photo = $media_items ? $media_items[0]['image'] : $this->media();
		$normalized_content = $this->normalize_legacy_content( $source_content );
		$unplaced_media = $this->unplaced_media_items( $media_items, $normalized_content, $mayor_photo );
		$data = array(
			$this->container( 'mayor-hu-header', array(
				$this->widget( 'mayor-hu-header-widget', 'agris-site-header', $this->header_settings( $page, 'hu', $routes, 'mayor-hu-header' ) ),
				$this->widget( 'mayor-hu-search-modal', 'agris-search-box', $this->search_settings( 'hu' ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'mayor-hu-hero', array(
				$this->widget( 'mayor-hu-hero-widget', 'agris-page-hero', array( 'kicker' => 'Polgármesteri Hivatal', 'title' => get_the_title( $page ), 'description' => '', 'parent_label' => 'Kezdőlap', 'parent_link' => $this->link( $routes['home_hu'] ), 'current_label' => get_the_title( $page ), 'background' => $mayor_photo ) ),
			), array( 'content_width' => 'full' ) ),
			$this->container( 'mayor-hu-profile', array(
				$this->widget( 'mayor-hu-profile-widget', 'agris-person-profile', array( 'photo' => $mayor_photo, 'role' => 'Polgármester', 'name' => 'Szabó Elek', 'subtitle' => '', 'bio' => '<p><strong>Születési idő:</strong> 1963. október 3.</p>', 'phone' => '0261 878 111', 'email' => 'primar@comunaagris.ro', 'office' => 'Hétfő 9:00–11:00 · Csütörtök 9:00–11:00' ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '70', 'right' => '0', 'bottom' => '32', 'left' => '0', 'isLinked' => false ) ) ),
			$this->container( 'mayor-hu-content', array(
				$this->widget( 'mayor-hu-content-widget', 'agris-content-media', array( 'kicker' => '', 'title' => '', 'description' => '', 'content' => $normalized_content, 'image' => $this->media(), 'image_side' => 'right' ) ),
			), array( 'padding' => array( 'unit' => 'px', 'top' => '24', 'right' => '0', 'bottom' => '64', 'left' => '0', 'isLinked' => false ) ) ),
		);
		if ( $unplaced_media ) {
			$data[] = $this->container( 'mayor-hu-media', array( $this->widget( 'mayor-hu-media-widget', 'agris-photo-gallery', array( 'kicker' => 'Média', 'title' => 'Kapcsolódó képek', 'description' => '', 'items_list' => $this->repeater( 'mayor-hu-media-items', $unplaced_media ), 'columns' => count( $unplaced_media ) > 2 ? '3' : '2' ) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '64', 'left' => '0', 'isLinked' => false ) ) );
		}
		$data[] = $this->container( 'mayor-hu-footer', array(
			$this->widget( 'mayor-hu-footer-widget', 'agris-site-footer', $this->footer_settings( 'hu', $routes, 'mayor-hu-footer' ) ),
			$this->widget( 'mayor-hu-accessibility-widget', 'agris-accessibility', $this->accessibility_settings( 'hu' ) ),
		), array( 'content_width' => 'full' ) );
		return $data;
	}

	private function section( string $seed, string $kicker, string $title, string $description = '', string $button = '', string $url = '', string $theme = 'light', string $background = '' ): array {
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
						'theme'       => $theme,
						'button_text' => $button,
						'button_link' => $this->link( $url ),
					)
				),
			),
			array(
				'background_background' => $background ? 'classic' : '',
				'background_color' => $background,
				'padding' => array( 'unit' => 'px', 'top' => '80', 'right' => '0', 'bottom' => '30', 'left' => '0', 'isLinked' => false ),
			)
		);
	}
}
