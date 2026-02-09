<?php

namespace Knowledge\Integration\Elementor\Widgets;

use Knowledge\Infrastructure\FrontendRenderer;
use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class KnowledgeSearchWidget extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'knowledge_search';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return __( 'Knowledge Search', 'knowledge' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories() {
		return [ 'knowledge-base' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_ai_controls();
		$this->register_results_layout_controls();
		$this->register_layout_controls();
		$this->register_style_input_controls();
		$this->register_style_button_controls();
		$this->register_style_ai_controls();
		$this->register_style_divider_controls();
		$this->register_style_card_controls();
		$this->register_style_content_controls();
		$this->register_style_summary_controls();
		$this->register_style_tags_controls();
		$this->register_style_category_controls();
		$this->register_style_note_count_controls();
		$this->register_style_recheck_controls();
	}

	protected function register_content_controls() {
		$this->start_controls_section(
			'section_search_settings',
			[
				'label' => __( 'Search Settings', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label'   => __( 'Placeholder', 'knowledge' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Search knowledge...', 'knowledge' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => __( 'Button Text', 'knowledge' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Search', 'knowledge' ),
			]
		);

		$this->add_control(
			'search_mode',
			[
				'label'   => __( 'Search Mode', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => [
					'standard' => __( 'Standard (WP Search)', 'knowledge' ),
					'ai_chat'  => __( 'AI / Chat', 'knowledge' ),
				],
			]
		);

		$this->add_control(
			'show_other_content',
			[
				'label'        => __( 'Show Other Content', 'knowledge' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'knowledge' ),
				'label_off'    => __( 'Hide', 'knowledge' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Keep other page content visible when search results are displayed.', 'knowledge' ),
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'other_content_selector',
			[
				'label'       => __( 'Content Selector to Hide', 'knowledge' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '.elementor-widget-knowledge_archive, .knowledge-archive-wrapper',
				'description' => __( 'CSS selector for content to hide when "Show Other Content" is off. Default targets Knowledge Archive widgets.', 'knowledge' ),
				'condition'   => [
					'show_other_content' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_note_count_controls() {
		$this->start_controls_section(
			'section_style_note_count',
			[
				'label'     => __( 'Note Count', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_note_count' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'note_count_typography',
				'selector' => '{{WRAPPER}} .knowledge-indicator-notes',
			]
		);

		$this->add_control(
			'note_count_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-indicator-notes' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'note_count_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-indicator-notes' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'note_count_border',
				'selector' => '{{WRAPPER}} .knowledge-indicator-notes',
			]
		);

		$this->add_control(
			'note_count_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-indicator-notes' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'note_count_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-indicator-notes' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'note_count_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-indicator-notes' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_ai_controls() {
		$this->start_controls_section(
			'section_ai_config',
			[
				'label'     => __( 'AI Configuration', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'search_mode' => 'ai_chat',
				],
			]
		);

		$this->add_control(
			'ai_mode',
			[
				'label'   => __( 'AI Mode', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'combined',
				'options' => [
					'rag_only'            => __( 'RAG Only (Strict)', 'knowledge' ),
					'llm_only'            => __( 'LLM Only (Creative)', 'knowledge' ),
					'combined'            => __( 'Combined', 'knowledge' ),
					'combined_prioritised'=> __( 'Combined (Prioritised)', 'knowledge' ),
					'combined_balanced'   => __( 'Combined (Balanced)', 'knowledge' ),
				],
			]
		);

		// Get Knowledge Categories for the filter
		$categories = get_terms( [
			'taxonomy'   => 'kb_category',
			'hide_empty' => false,
		] );

		$cat_options = [];
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$cat_options[ $category->term_id ] = $category->name;
			}
		}

		$this->add_control(
			'category_filter',
			[
				'label'    => __( 'Filter by Category', 'knowledge' ),
				'type'     => Controls_Manager::SELECT2,
				'options'  => $cat_options,
				'multiple' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'input_size',
			[
				'label'   => __( 'Input Size', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => [
					'small'  => __( 'Small', 'knowledge' ),
					'medium' => __( 'Medium', 'knowledge' ),
					'large'  => __( 'Large', 'knowledge' ),
				],
			]
		);

		$this->add_control(
			'button_position',
			[
				'label'   => __( 'Button Position', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => [
					'inline'   => __( 'Inline', 'knowledge' ),
					'separate' => __( 'Separate', 'knowledge' ),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_input_controls() {
		$this->start_controls_section(
			'section_style_input',
			[
				'label' => __( 'Input Field', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .knowledge-search-input',
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label'     => __( 'Text Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .knowledge-search-input',
			]
		);

		$this->add_control(
			'input_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_button_controls() {
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => __( 'Button', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .knowledge-search-button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'knowledge' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => __( 'Text Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'knowledge' ),
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label'     => __( 'Text Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-search-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .knowledge-search-button',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-search-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-search-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_ai_controls() {
		$this->start_controls_section(
			'section_style_ai',
			[
				'label'     => __( 'AI Response Box', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'search_mode' => 'ai_chat',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'ai_response_typography',
				'selector' => '{{WRAPPER}} .knowledge-ai-response',
			]
		);

		$this->add_control(
			'ai_response_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-ai-response' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'ai_response_border',
				'selector' => '{{WRAPPER}} .knowledge-ai-response',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'ai_response_box_shadow',
				'selector' => '{{WRAPPER}} .knowledge-ai-response',
			]
		);

		$this->add_responsive_control(
			'ai_response_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-ai-response' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_divider_controls() {
		$this->start_controls_section(
			'section_style_divider',
			[
				'label'     => __( 'Results Divider', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_divider',
			[
				'label'     => __( 'Show Divider', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label'   => __( 'Style', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid'  => __( 'Solid', 'knowledge' ),
					'double' => __( 'Double', 'knowledge' ),
					'dotted' => __( 'Dotted', 'knowledge' ),
					'dashed' => __( 'Dashed', 'knowledge' ),
				],
				'selectors' => [
					'{{WRAPPER}} .knowledge-results-divider' => 'border-top-style: {{VALUE}};',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e5e7eb',
				'selectors' => [
					'{{WRAPPER}} .knowledge-results-divider' => 'border-top-color: {{VALUE}};',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label'     => __( 'Weight', 'knowledge' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .knowledge-results-divider' => 'border-top-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->add_control(
			'divider_width',
			[
				'label'     => __( 'Width', 'knowledge' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 100,
					'unit' => '%',
				],
				'size_units' => [ '%', 'px' ],
				'selectors' => [
					'{{WRAPPER}} .knowledge-results-divider' => 'width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->add_control(
			'divider_gap',
			[
				'label'     => __( 'Gap', 'knowledge' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 32,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .knowledge-results-divider' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_results_layout_controls() {
		$this->start_controls_section(
			'section_results_layout',
			[
				'label'     => __( 'Results Layout', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'           => __( 'Columns', 'knowledge' ),
				'type'            => Controls_Manager::SELECT,
				'desktop_default' => 3,
				'tablet_default'  => 2,
				'mobile_default'  => 1,
				'options'         => [
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
				],
				'selectors'       => [
					'{{WRAPPER}} .knowledge-archive-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);
		
		$this->add_responsive_control(
			'column_gap',
			[
				'label'     => __( 'Columns Gap', 'knowledge' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 32,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .knowledge-archive-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'     => __( 'Rows Gap', 'knowledge' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 32,
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .knowledge-archive-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'title_length',
			[
				'label'       => __( 'Title Length (Characters)', 'knowledge' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'description' => __( '0 or empty to show full title.', 'knowledge' ),
			]
		);
		
		$this->add_control(
			'show_image',
			[
				'label'     => __( 'Show Image', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'knowledge' ),
				'label_off' => __( 'Hide', 'knowledge' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'show_summary',
			[
				'label'     => __( 'Show Summary', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'summary_length',
			[
				'label'       => __( 'Summary Length (Words)', 'knowledge' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'condition'   => [
					'show_summary' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_category',
			[
				'label'     => __( 'Show Category', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'category_position',
			[
				'label'     => __( 'Category Position', 'knowledge' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => [
					'inline'    => __( 'Inline (Below Title)', 'knowledge' ),
					'top_right' => __( 'Top Right (Over Image)', 'knowledge' ),
				],
				'condition' => [
					'show_category' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_note_count',
			[
				'label'     => __( 'Show Note Count', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'note_count_position',
			[
				'label'     => __( 'Note Count Position', 'knowledge' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top_right',
				'options'   => [
					'top_left'  => __( 'Top Left', 'knowledge' ),
					'top_right' => __( 'Top Right', 'knowledge' ),
				],
				'condition' => [
					'show_note_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'note_count_icon',
			[
				'label'     => __( 'Show Icon', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'show_note_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'note_count_label',
			[
				'label'     => __( 'Label', 'knowledge' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'placeholder' => __( 'e.g. Notes', 'knowledge' ),
				'condition' => [
					'show_note_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_badges',
			[
				'label'     => __( 'Show Badges (Tags)', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'show_meta',
			[
				'label'     => __( 'Show Meta', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'show_avatar',
			[
				'label'     => __( 'Show Avatar', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
			]
		);

		$this->add_control(
			'show_recheck_button',
			[
				'label'     => __( 'Show Re-check Button', 'knowledge' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'knowledge' ),
				'label_off' => __( 'Hide', 'knowledge' ),
				'default'   => 'yes',
				'description' => __( 'Display a "Re-check" button in the card hover state to request article update.', 'knowledge' ),
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_card_controls() {
		$this->start_controls_section(
			'section_style_card',
			[
				'label'     => __( 'Results Card', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .knowledge-card',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .knowledge-card',
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_content_controls() {
		$this->start_controls_section(
			'section_style_content',
			[
				'label'     => __( 'Results Content', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_title',
			[
				'label' => __( 'Title', 'knowledge' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .knowledge-card-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-title, {{WRAPPER}} .knowledge-card-title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_content_box',
			[
				'label'     => __( 'Content Box', 'knowledge' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_summary_controls() {
		$this->start_controls_section(
			'section_style_summary',
			[
				'label'     => __( 'Results Summary', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_summary' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'summary_typography',
				'selector' => '{{WRAPPER}} .knowledge-card-summary',
			]
		);

		$this->add_control(
			'summary_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-summary' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'summary_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-summary' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_tags_controls() {
		$this->start_controls_section(
			'section_style_tags',
			[
				'label'     => __( 'Results Tags', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_badges' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tag_typography',
				'selector' => '{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge',
			]
		);

		$this->add_control(
			'tag_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'tag_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'tag_border',
				'selector' => '{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge',
			]
		);

		$this->add_control(
			'tag_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tag_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tag_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-hover-tags .knowledge-card-badge' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_category_controls() {
		$this->start_controls_section(
			'section_style_category',
			[
				'label'     => __( 'Results Category', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_category' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'category_typography',
				'selector' => '{{WRAPPER}} .knowledge-card-category',
			]
		);

		$this->add_control(
			'category_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-category' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'category_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-card-category' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'category_border',
				'selector' => '{{WRAPPER}} .knowledge-card-category',
			]
		);

		$this->add_control(
			'category_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-category' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'category_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'category_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-card-category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_recheck_controls() {
		$this->start_controls_section(
			'section_style_recheck',
			[
				'label'     => __( 'Re-check Button', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_recheck_button' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'recheck_typography',
				'selector' => '{{WRAPPER}} .knowledge-recheck-btn',
			]
		);

		$this->add_control(
			'recheck_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-recheck-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'recheck_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-recheck-btn' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'recheck_hover_color',
			[
				'label'     => __( 'Hover Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-recheck-btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'recheck_hover_bg_color',
			[
				'label'     => __( 'Hover Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-recheck-btn:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'recheck_border',
				'selector' => '{{WRAPPER}} .knowledge-recheck-btn',
			]
		);

		$this->add_control(
			'recheck_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-recheck-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'recheck_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-recheck-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'recheck_margin',
			[
				'label'      => __( 'Margin', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-recheck-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$placeholder = $settings['placeholder'];
		$button_text = $settings['button_text'];
		$search_mode = $settings['search_mode'];
		
		// Wrapper classes
		$wrapper_classes = [ 'knowledge-search-wrapper' ];
		$wrapper_classes[] = 'size-' . $settings['input_size'];
		$wrapper_classes[] = 'pos-' . $settings['button_position'];
		
		if ( $search_mode === 'ai_chat' ) {
			$wrapper_classes[] = 'mode-ai';
		} else {
			$wrapper_classes[] = 'mode-standard';
		}

		echo '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"';
		
		$display_options = [
			'title_length'      => $settings['title_length'],
			'show_image'        => $settings['show_image'],
			'show_summary'      => $settings['show_summary'],
			'summary_length'    => $settings['summary_length'],
			'show_category'     => $settings['show_category'],
			'category_position' => $settings['category_position'],
			'show_badges'       => $settings['show_badges'],
			'show_meta'         => $settings['show_meta'],
			'show_avatar'       => $settings['show_avatar'],
			'show_recheck_button' => isset( $settings['show_recheck_button'] ) ? $settings['show_recheck_button'] : 'yes',
			'show_note_count'      => isset($settings['show_note_count']) ? $settings['show_note_count'] : 'yes',
			'note_count_position'  => isset($settings['note_count_position']) ? $settings['note_count_position'] : 'top_right',
			'note_count_icon'      => isset($settings['note_count_icon']) ? $settings['note_count_icon'] : 'yes',
			'note_count_label'     => isset($settings['note_count_label']) ? $settings['note_count_label'] : '',
		];

		// Add Data Attributes
		if ( $search_mode === 'ai_chat' ) {
			echo ' data-knowledge-search-mode="ai"';
			echo ' data-ai-mode="' . esc_attr( $settings['ai_mode'] ) . '"';
			
			if ( ! empty( $settings['category_filter'] ) ) {
				echo ' data-categories="' . esc_attr( json_encode( $settings['category_filter'] ) ) . '"';
			}
		} else {
			echo ' data-knowledge-search-mode="standard-ajax"';
		}

		echo ' data-display-options="' . esc_attr( json_encode( $display_options ) ) . '"';

		// Other Content Settings
		$show_other = isset( $settings['show_other_content'] ) ? $settings['show_other_content'] : 'yes';
		echo ' data-show-other-content="' . esc_attr( $show_other ) . '"';
		
		if ( 'yes' !== $show_other ) {
			$selector = isset( $settings['other_content_selector'] ) ? $settings['other_content_selector'] : '.elementor-widget-knowledge_archive, .knowledge-archive-wrapper';
			echo ' data-other-content-selector="' . esc_attr( $selector ) . '"';
		}
		
		echo '>';

		// Render Form
		$action = esc_url( home_url( '/' ) );
		$query  = get_search_query();
		
		// Prevent default form submission in JS for both modes
		$form_class = 'knowledge-search-form knowledge-ai-form';

		printf(
			'<form role="search" method="get" class="%s" action="%s">
				<input type="hidden" name="post_type" value="kb_article" />
				<input type="search" class="knowledge-search-input" placeholder="%s" value="%s" name="s" />
				<button type="submit" class="knowledge-search-button">%s</button>
			</form>',
			esc_attr( $form_class ),
			$action,
			esc_attr( $placeholder ),
			esc_attr( $query ),
			esc_html( $button_text )
		);

		// Render Response Container
		echo '<div class="knowledge-ai-response" style="display:none;">';
		if ( $search_mode === 'ai_chat' ) {
			echo '<div class="knowledge-ai-content"></div>';
			echo '<div class="knowledge-ai-provenance"></div>';
		}
		
		echo '<div class="knowledge-ai-results knowledge-archive-grid"></div>';
		
		if ( 'yes' === $settings['show_divider'] ) {
			echo '<div class="knowledge-results-divider" style="display:none;"></div>';
		}

		echo '</div>';

		echo '</div>';
	}
}
