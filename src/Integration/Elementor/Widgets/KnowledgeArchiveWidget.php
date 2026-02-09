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

class KnowledgeArchiveWidget extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'knowledge_archive';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return __( 'Knowledge Archive', 'knowledge' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
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
		$this->register_query_controls();
		$this->register_layout_controls();
		$this->register_style_controls();
		$this->register_style_note_count_controls();
		$this->register_style_recheck_controls();
	}

	protected function register_query_controls() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => __( 'Limit', 'knowledge' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);
		
		$this->add_control(
			'orderby',
			[
				'label'   => __( 'Order By', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date'     => __( 'Date', 'knowledge' ),
					'title'    => __( 'Title', 'knowledge' ),
					'modified' => __( 'Last Modified', 'knowledge' ),
					'rand'     => __( 'Random', 'knowledge' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => __( 'Order', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC'  => __( 'ASC', 'knowledge' ),
					'DESC' => __( 'DESC', 'knowledge' ),
				],
			]
		);

		// Taxonomy Filters (simplified for now, full Select2 usually requires more boilerplate or Helpers)
		// We'll add basic Category selection if possible, or skip for V1 if complex.
		// For now, let's stick to basic query params.

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
			'pagination_type',
			[
				'label'   => __( 'Pagination', 'knowledge' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'            => __( 'None', 'knowledge' ),
					'numeric'         => __( 'Numeric (Standard)', 'knowledge' ),
					'load_more'       => __( 'Load More Button', 'knowledge' ),
					'infinite_scroll' => __( 'Endless Scroll', 'knowledge' ),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'load_more_label',
			[
				'label'     => __( 'Load More Label', 'knowledge' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load More', 'knowledge' ),
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);
		
		$this->add_control(
			'loading_text',
			[
				'label'     => __( 'Loading Text', 'knowledge' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Loading...', 'knowledge' ),
				'condition' => [
					'pagination_type' => [ 'load_more', 'infinite_scroll' ],
				],
			]
		);

		$this->add_control(
			'no_more_posts_text',
			[
				'label'     => __( 'No More Posts Text', 'knowledge' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'No more posts', 'knowledge' ),
				'condition' => [
					'pagination_type' => [ 'load_more', 'infinite_scroll' ],
				],
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

	protected function register_style_controls() {
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => __( 'Card', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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

		$this->start_controls_section(
			'section_style_content',
			[
				'label' => __( 'Content', 'knowledge' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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

		$this->start_controls_section(
			'section_style_summary',
			[
				'label'     => __( 'Summary', 'knowledge' ),
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

		$this->start_controls_section(
			'section_style_tags',
			[
				'label'     => __( 'Tags', 'knowledge' ),
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

		$this->start_controls_section(
			'section_style_category',
			[
				'label'     => __( 'Category', 'knowledge' ),
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

		$this->start_controls_section(
			'section_style_pagination',
			[
				'label'     => __( 'Pagination', 'knowledge' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'pagination_type!' => 'none',
				],
			]
		);

		// Numeric Pagination Styles
		$this->add_control(
			'heading_numeric_pagination',
			[
				'label'     => __( 'Numeric Pagination', 'knowledge' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'pagination_type' => 'numeric',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_typography',
				'selector'  => '{{WRAPPER}} .knowledge-pagination .page-numbers',
				'condition' => [
					'pagination_type' => 'numeric',
				],
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label'     => __( 'Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-pagination .page-numbers' => 'color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'numeric',
				],
			]
		);

		$this->add_control(
			'pagination_active_color',
			[
				'label'     => __( 'Active Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-pagination .page-numbers.current' => 'color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'numeric',
				],
			]
		);

		// Load More Button Styles
		$this->add_control(
			'heading_load_more_btn',
			[
				'label'     => __( 'Load More Button', 'knowledge' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'pagination_type' => 'load_more',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'load_more_typography',
				'selector'  => '{{WRAPPER}} .knowledge-load-more-btn',
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_load_more_btn_style' );

		$this->start_controls_tab(
			'tab_load_more_btn_normal',
			[
				'label'     => __( 'Normal', 'knowledge' ),
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'load_more_btn_color',
			[
				'label'     => __( 'Text Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-load-more-btn' => 'color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'load_more_btn_bg_color',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-load-more-btn' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'load_more_btn_border',
				'selector'  => '{{WRAPPER}} .knowledge-load-more-btn',
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_load_more_btn_hover',
			[
				'label'     => __( 'Hover', 'knowledge' ),
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'load_more_btn_color_hover',
			[
				'label'     => __( 'Text Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-load-more-btn:hover' => 'color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'load_more_btn_bg_color_hover',
			[
				'label'     => __( 'Background Color', 'knowledge' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .knowledge-load-more-btn:hover' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'load_more_btn_border_hover',
				'selector'  => '{{WRAPPER}} .knowledge-load-more-btn:hover',
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'load_more_btn_padding',
			[
				'label'      => __( 'Padding', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'pagination_type' => 'load_more',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'load_more_btn_border_radius',
			[
				'label'      => __( 'Border Radius', 'knowledge' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .knowledge-load-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'pagination_type' => 'load_more',
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

		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
		$paged = $paged ? $paged : 1;

		$args = [
			'post_type'      => 'kb_article',
			'posts_per_page' => $settings['posts_per_page'],
			'orderby'        => $settings['orderby'],
			'order'          => $settings['order'],
			'paged'          => $paged,
		];

		// Add taxonomy filters if implemented later
		// For now, we only have basic args

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			// Prepare attributes for JS
			$pagination_type = $settings['pagination_type'];
			$max_pages = $query->max_num_pages;
			
			// Exclude paged from query args for JS
			$js_query_args = $args;
			unset( $js_query_args['paged'] );
			
			// Display Options
			$options = [
				'show_image'        => 'yes' === $settings['show_image'],
				'title_length'      => $settings['title_length'],
				'show_summary'      => 'yes' === $settings['show_summary'],
				'summary_length'    => $settings['summary_length'],
				'show_category'     => 'yes' === $settings['show_category'],
				'category_position' => $settings['category_position'],
				'show_badges'       => 'yes' === $settings['show_badges'],
				'show_meta'         => 'yes' === $settings['show_meta'],
				'show_avatar'       => 'yes' === $settings['show_avatar'],
				'show_recheck_button' => 'yes' === $settings['show_recheck_button'],
				'show_note_count'      => 'yes' === $settings['show_note_count'],
				'note_count_position'  => $settings['note_count_position'],
				'note_count_icon'      => 'yes' === $settings['note_count_icon'],
				'note_count_label'     => $settings['note_count_label'],
			];
			
			// Wrapper Attributes
			$wrapper_attrs = [
				'class' => 'knowledge-archive-wrapper',
				'data-pagination-type' => $pagination_type,
				'data-page' => $paged,
				'data-max-pages' => $max_pages,
				'data-nonce' => wp_create_nonce( 'knowledge_load_more' ),
				'data-query-args' => htmlspecialchars( json_encode( $js_query_args ), ENT_QUOTES, 'UTF-8' ),
				'data-options' => htmlspecialchars( json_encode( $options ), ENT_QUOTES, 'UTF-8' ),
			];
			
			if ( in_array( $pagination_type, [ 'load_more', 'infinite_scroll' ] ) ) {
				$wrapper_attrs['data-load-more-label'] = $settings['load_more_label'] ?? __( 'Load More', 'knowledge' );
				$wrapper_attrs['data-loading-text'] = $settings['loading_text'] ?? __( 'Loading...', 'knowledge' );
				$wrapper_attrs['data-no-more-posts-text'] = $settings['no_more_posts_text'] ?? __( 'No more posts', 'knowledge' );
			}
			
			$attr_string = '';
			foreach ( $wrapper_attrs as $name => $value ) {
				$attr_string .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
			}
			
			echo '<div' . $attr_string . '>';
			
			// Grid
			$grid_attrs = [
				'class' => 'knowledge-archive-grid',
				'data-columns' => $settings['columns'],
			];
			$grid_attr_string = '';
			foreach ( $grid_attrs as $name => $value ) {
				$grid_attr_string .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
			}
			
			echo '<div' . $grid_attr_string . '>';

			while ( $query->have_posts() ) {
				$query->the_post();
				echo FrontendRenderer::render_card( get_post(), $options );
			}
			echo '</div>'; // End grid

			// Render Pagination Controls
			if ( $max_pages > 1 ) {
				if ( 'numeric' === $pagination_type ) {
					echo '<div class="knowledge-pagination">';
					echo paginate_links( [
						'total'     => $max_pages,
						'current'   => $paged,
						'prev_text' => __( '&laquo; Prev', 'knowledge' ),
						'next_text' => __( 'Next &raquo;', 'knowledge' ),
					] );
					echo '</div>';
				} elseif ( 'load_more' === $pagination_type ) {
					echo '<div class="knowledge-pagination">';
					echo sprintf( 
						'<button class="knowledge-load-more-btn" data-action="load-more">%s</button>',
						esc_html( $settings['load_more_label'] )
					);
					echo '</div>';
				} elseif ( 'infinite_scroll' === $pagination_type ) {
					echo '<div class="knowledge-pagination">';
					echo '<div class="knowledge-infinite-scroll-sentinel"></div>';
					echo sprintf( 
						'<div class="knowledge-loading-spinner" style="display:none;"></div><span class="knowledge-loading-text" style="display:none;">%s</span>', 
						esc_html( $settings['loading_text'] ) 
					);
					echo sprintf( '<div class="knowledge-end-message" style="display:none;">%s</div>', esc_html( $settings['no_more_posts_text'] ) );
					echo '</div>';
				}
			}
			
			echo '</div>'; // End wrapper

			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No articles found.', 'knowledge' ) . '</p>';
		}
	}
}
