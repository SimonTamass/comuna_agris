<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Site_Header extends Base {
	public function get_name(): string { return 'agris-site-header'; }
	public function get_title(): string { return __( '01 · Header complet', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-header'; }

	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Identitate și navigație', 'comuna-agris' ) ) );
		$this->add_control( 'official_text', array( 'label' => __( 'Text bară oficială', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Site oficial al Primăriei Comunei Agriș, județul Satu Mare, România', 'label_block' => true ) );
		$this->add_control( 'trust_text', array( 'label' => __( 'Insignă încredere', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Conexiune securizată' ) );
		$this->add_control( 'mail_url', array( 'label' => __( 'Link Mail', 'comuna-agris' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://' ) );
		$this->add_control( 'logo', array( 'label' => __( 'Logo', 'comuna-agris' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'logo', 'default' => 'thumbnail' ) );
		$this->add_control( 'brand_title', array( 'label' => __( 'Nume instituție', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Comuna Agriș' ) );
		$this->add_control( 'brand_subtitle', array( 'label' => __( 'Subtitlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria · Egri Község' ) );
		$this->add_control( 'home_url', array( 'label' => __( 'Link logo', 'comuna-agris' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'menu_id', array( 'label' => __( 'Meniu WordPress', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => self::menus() ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Text buton', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Monitorul Oficial' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Link buton', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'sticky', array( 'label' => __( 'Header fix la derulare', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'languages', array( 'label' => __( 'Limbi', 'comuna-agris' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'code', array( 'label' => __( 'Cod', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'RO' ) );
		$rep->add_control( 'label', array( 'label' => __( 'Denumire', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Română' ) );
		$rep->add_control( 'url', array( 'label' => __( 'Link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'language_items', array( 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'title_field' => '{{{ code }}} · {{{ label }}}', 'default' => array( array( 'code' => 'RO', 'label' => 'Română' ), array( 'code' => 'HU', 'label' => 'Magyar' ) ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$current = $s['language_items'][0] ?? array( 'code' => 'RO', 'label' => 'Română' );
		?>
		<div class="agris-header-wrap <?php echo 'yes' === $s['sticky'] ? 'is-sticky' : ''; ?>">
			<a class="agris-skip-link" href="#main-content"><?php esc_html_e( 'Sari la conținut', 'comuna-agris' ); ?></a>
			<div class="agris-govbar"><div class="agris-shell agris-govbar-inner">
				<div class="agris-official"><span class="agris-flag agris-flag-ro" aria-hidden="true"><i></i><i></i><i></i></span><span><?php echo esc_html( $s['official_text'] ); ?></span><?php if ( $s['trust_text'] ) : ?><span class="agris-trust"><?php echo esc_html( $s['trust_text'] ); ?></span><?php endif; ?></div>
				<div class="agris-gov-actions"><?php if ( ! empty( $s['mail_url']['url'] ) ) : ?><a <?php echo self::link_attrs( $s['mail_url'] ); ?>>Mail</a><?php endif; ?>
					<div class="agris-lang"><button type="button" class="agris-lang-trigger" aria-expanded="false"><span class="agris-flag agris-flag-ro"><i></i><i></i><i></i></span><?php echo esc_html( $current['code'] ); ?></button>
						<div class="agris-lang-menu"><?php foreach ( $s['language_items'] as $item ) : ?><a <?php echo self::link_attrs( $item['url'] ); ?>><strong><?php echo esc_html( $item['code'] ); ?></strong><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?></div>
					</div>
				</div>
			</div></div>
			<header class="agris-site-header"><div class="agris-shell agris-header-inner">
				<a class="agris-brand" <?php echo self::link_attrs( $s['home_url'] ); ?>>
					<?php if ( ! empty( $s['logo']['id'] ) ) : ?><span class="agris-brand-logo"><?php echo Group_Control_Image_Size::get_attachment_image_html( $s, 'logo', 'logo' ); ?></span><?php else : ?><span class="agris-brand-mark">CA</span><?php endif; ?>
					<span><strong><?php echo esc_html( $s['brand_title'] ); ?></strong><small><?php echo esc_html( $s['brand_subtitle'] ); ?></small></span>
				</a>
				<nav class="agris-main-nav" aria-label="<?php esc_attr_e( 'Navigație principală', 'comuna-agris' ); ?>">
				<?php
				if ( $s['menu_id'] ) {
					wp_nav_menu( array( 'menu' => (int) $s['menu_id'], 'container' => false, 'menu_class' => 'agris-menu', 'fallback_cb' => false, 'depth' => 2 ) );
				} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<div class="agris-editor-note">' . esc_html__( 'Selectați un meniu WordPress în panoul din stânga.', 'comuna-agris' ) . '</div>';
				}
				?>
				</nav>
				<div class="agris-header-actions"><button class="agris-icon-button" type="button" data-agris-search aria-label="<?php esc_attr_e( 'Caută', 'comuna-agris' ); ?>">⌕</button><?php if ( $s['cta_text'] ) : ?><a class="agris-button agris-button-primary" <?php echo self::link_attrs( $s['cta_link'] ); ?>><?php echo esc_html( $s['cta_text'] ); ?></a><?php endif; ?><button class="agris-icon-button agris-nav-toggle" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Deschide meniul', 'comuna-agris' ); ?>">☰</button></div>
			</div></header>
		</div>
		<?php
	}
}
