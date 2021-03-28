<?php
namespace ElementorWpRentals\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Wprentals_Categories_List extends Widget_Base {

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
		return 'Wprentals_Categories_List';
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
		return __( 'WpRentals Categories List', 'rentals-elementor' );
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
		return 'eicon-product-categories';
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
     
    public function elementor_transform($input){
            $output=array();
            if( is_array($input) ){
                foreach ($input as $key=>$tax){
                    $output[$tax['value']]=$tax['label'];
                }
            }
            return $output;
        }

        protected function _register_controls() {
                global $all_tax;
                $all_tax_elemetor=$this->elementor_transform($all_tax);
               
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'rentals-elementor' ),
			]
		);

		
          
                
              $this->add_control(
			'place_list',
			[
                            'label' => __( 'Type the category name you want to show', 'rentals-elementor' ),
                            'label_block'=>true,
                            'type' => \Elementor\Controls_Manager::SELECT2,
                            'multiple' => true,
                            'options' => $all_tax_elemetor,
			]
		);
              
                $this->add_control(
			'place_per_row',
			[
                            'label' => __( 'Categories per row', 'rentals-elementor' ),
                            'type' => Controls_Manager::TEXT,
			]
		);
                 
               $this->add_control(
			'spaces_unit',
			[
                            'label' => __( 'Space between units(only numbers,in pixels)', 'rentals-elementor' ),
                            'type' => Controls_Manager::TEXT,
                            'default'=>'8',
                               'label_block'=>true,
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
        
         public function wpresidence_send_to_shortcode($input){
            $output='';
            if($input!==''){
                $numItems = count($input);
                $i = 0;

                foreach ($input as $key=>$value){
                    $output.=$value;
                    if(++$i !== $numItems) {
                      $output.=', ';
                    }
                }
            }
            return $output;
        }
        
	protected function render() {
            $settings = $this->get_settings_for_display();

            $attributes['place_list']          =  $this -> wpresidence_send_to_shortcode( $settings['place_list'] );  
            $attributes['place_per_row']       =   $settings['place_per_row'];
            $attributes['spaces_unit']         =   $settings['spaces_unit'];
                
            echo  wpestate_places_list_function($attributes);
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
