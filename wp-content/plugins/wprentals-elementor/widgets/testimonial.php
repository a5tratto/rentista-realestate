<?php
namespace ElementorWpRentals\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Wprentals_Testimonial extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'WpRentals_Testimonial';
	}

        public function get_categories() {
		return [ 'wprentals' ];
	}
        
        
	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'WpRentals Testimonial', 'rentals-elementor' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-testimonial';
	}

	

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
	return [ '' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
     


        protected function _register_controls() {
                    
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'rentals-elementor' ),
			]
		);

		
                
                $this->add_control(
			'client_name',
			[
                            'label' => __( 'Client Name', 'rentals-elementor' ),
                            'type' => Controls_Manager::TEXT,
			]
		);
                
                $this->add_control(
			'title_client',
			[
                            'label' => __( 'Title Client', 'rentals-elementor' ),
                            'type' => Controls_Manager::TEXT,
			]
		);
                
                $this->add_control(
			'imagelinks',
			[
                            'label' => __( 'Image', 'rentals-elementor' ),
                            'type' => Controls_Manager::MEDIA,
			]
		);
                
                
                $this->add_control(
			'testimonial_text',
			[
                            'label' => __( 'Testimonial Text ', 'rentals-elementor' ),
                            'type' => Controls_Manager::TEXTAREA,
			]
		);
                
              
		$this->end_controls_section();

		
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
        
      
	protected function render() {
		$settings = $this->get_settings_for_display();
                
                $attributes['client_name']          =   $settings['client_name'];
                $attributes['title_client']         =   $settings['title_client'];
                $attributes['imagelinks']           =   $settings['imagelinks']['url'];
                $attributes['testimonial_text']     =   $settings['testimonial_text'];
              
               
                
                
              echo  wpestate_testimonial_function($attributes);
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<div class="title">
			{{{ settings.title }}}
		</div>
		<?php
	}
}
