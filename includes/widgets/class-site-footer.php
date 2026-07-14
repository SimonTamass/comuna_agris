<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Site_Footer extends Base {
	public function get_name(): string { return 'agris-site-footer'; }
	public function get_title(): string { return __( '02 · Footer complet', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-footer'; }

	protected function register_controls(): void {
		$this->start_controls_section( 'brand', array( 'label' => __( 'Instituție', 'comuna-agris' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Comuna Agriș' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'Subtitlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria · Egri Község' ) );
		$this->add_control( 'description', array( 'label' => __( 'Descriere', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Portal oficial pentru cetățeni, documente publice și comunicări administrative.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'columns', array( 'label' => __( 'Coloane de linkuri', 'comuna-agris' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'column', array( 'label' => __( 'Coloană', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Primăria' ) );
		$rep->add_control( 'label', array( 'label' => __( 'Text', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Conducere' ) );
		$rep->add_control( 'url', array( 'label' => __( 'Link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'links', array( 'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(), 'title_field' => '{{{ column }}} · {{{ label }}}', 'default' => array( array( 'column' => 'Primăria', 'label' => 'Conducere' ), array( 'column' => 'Primăria', 'label' => 'Consiliul Local' ), array( 'column' => 'Informații publice', 'label' => 'Anunțuri' ), array( 'column' => 'Informații publice', 'label' => 'Monitorul Oficial' ) ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'contact', array( 'label' => __( 'Contact', 'comuna-agris' ) ) );
		$this->add_control( 'phone', array( 'label' => __( 'Telefon', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => '0261 878 112' ) );
		$this->add_control( 'email', array( 'label' => __( 'Email', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'primaria@comunaagris.ro' ) );
		$this->add_control( 'address', array( 'label' => __( 'Adresă', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'România, cod 447066, Agriș, str. Csury Balint, nr. 68, Satu Mare' ) );
		$this->add_control( 'copyright', array( 'label' => __( 'Copyright', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Toate drepturile rezervate Comuna Agriș.' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$columns = array();
		foreach ( $s['links'] as $item ) { $columns[ $item['column'] ][] = $item; }
		?>
		<footer class="agris-footer"><div class="agris-shell agris-footer-grid">
			<div class="agris-footer-brand"><div class="agris-brand"><span class="agris-brand-mark">CA</span><span><strong><?php echo esc_html( $s['title'] ); ?></strong><small><?php echo esc_html( $s['subtitle'] ); ?></small></span></div><p><?php echo esc_html( $s['description'] ); ?></p></div>
			<?php foreach ( $columns as $title => $items ) : ?><div><h2><?php echo esc_html( $title ); ?></h2><div class="agris-footer-links"><?php foreach ( $items as $item ) : ?><a <?php echo self::link_attrs( $item['url'] ); ?>><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?></div></div><?php endforeach; ?>
			<div><h2><?php esc_html_e( 'Contact', 'comuna-agris' ); ?></h2><div class="agris-footer-contact"><a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone'] ) ); ?>"><?php echo esc_html( $s['phone'] ); ?></a><a href="mailto:<?php echo esc_attr( antispambot( $s['email'] ) ); ?>"><?php echo esc_html( antispambot( $s['email'] ) ); ?></a><p><?php echo nl2br( esc_html( $s['address'] ) ); ?></p></div></div>
		</div><div class="agris-shell agris-footer-bottom"><span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $s['copyright'] ); ?></span><a href="#top"><?php esc_html_e( 'Înapoi sus ↑', 'comuna-agris' ); ?></a></div></footer>
		<?php
	}
}
