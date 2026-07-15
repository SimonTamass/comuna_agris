<?php
namespace ComunaAgris\Widgets;

use ComunaAgris\Legacy_Content;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Single_Post extends Base {
	public function get_name(): string {
		return 'agris-single-post';
	}

	public function get_title(): string {
		return __( '23 · Egyedi blogbejegyzés', 'comuna-agris' );
	}

	public function get_icon(): string {
		return 'eicon-single-post';
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'layout', array( 'label' => __( 'Tartalom', 'comuna-agris' ) ) );
		$this->add_control( 'show_image', array( 'label' => __( 'Kiemelt kép', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_author', array( 'label' => __( 'Szerző', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->add_control( 'show_share', array( 'label' => __( 'Megosztás', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'home_label', array( 'label' => __( 'Kezdőlap felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Acasă' ) );
		$this->add_control( 'home_url', array( 'label' => __( 'Kezdőlap hivatkozása', 'comuna-agris' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'article_label', array( 'label' => __( 'Bejegyzés felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Articol' ) );
		$this->add_control( 'share_label', array( 'label' => __( 'Megosztás felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Distribuie' ) );
		$this->add_control( 'copy_label', array( 'label' => __( 'Másolás felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Copiază linkul' ) );
		$this->end_controls_section();
		$this->register_common_style_controls();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			echo '<div class="agris-empty">' . esc_html__( 'Acest widget se afișează într-un șablon pentru articol individual.', 'comuna-agris' ) . '</div>';
			return;
		}

		$categories = get_the_category( $post_id );
		$content = Legacy_Content::normalize( (string) get_post_field( 'post_content', $post_id ) );
		?>
		<article class="agris-single">
			<header>
				<div class="agris-breadcrumbs">
					<a <?php echo self::link_attrs( $settings['home_url'] ); ?>><?php echo esc_html( $settings['home_label'] ); ?></a><span>/</span><span><?php echo esc_html( $categories[0]->name ?? $settings['article_label'] ); ?></span>
				</div>
				<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
				<div class="agris-single-meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>
					<?php if ( 'yes' === $settings['show_author'] ) : ?><span><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ); ?></span><?php endif; ?>
					<?php if ( $categories ) : ?><a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a><?php endif; ?>
				</div>
			</header>
			<?php if ( 'yes' === $settings['show_image'] && has_post_thumbnail( $post_id ) ) : ?>
				<figure class="agris-single-image"><?php echo get_the_post_thumbnail( $post_id, 'full' ); ?></figure>
			<?php endif; ?>
			<div class="agris-single-layout">
				<div class="agris-single-content"><?php echo apply_filters( 'the_content', $content ); ?></div>
				<?php if ( 'yes' === $settings['show_share'] ) : ?>
					<aside class="agris-share">
						<strong><?php echo esc_html( $settings['share_label'] ); ?></strong>
						<a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Facebook</a>
						<a href="mailto:?subject=<?php echo rawurlencode( get_the_title( $post_id ) ); ?>&body=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Email</a>
						<button type="button" data-agris-copy="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( $settings['copy_label'] ); ?></button>
					</aside>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}
}
