/**
 * Lumina Instagram Feed — Gutenberg block editor script.
 */
( function ( blocks, blockEditor, components, i18n, element ) {
	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var RangeControl = components.RangeControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var Placeholder = components.Placeholder;

	blocks.registerBlockType( 'lumina/instagram-feed', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'div',
				{ className: props.className },
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Feed Settings', 'lumina-instagram-feed' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Use global design from Design Studio', 'lumina-instagram-feed' ),
							checked: attributes.useGlobalDesign,
							onChange: function ( val ) {
								setAttributes( { useGlobalDesign: val } );
							},
						} ),
						el( RangeControl, {
							label: __( 'Number of posts (0 = global default)', 'lumina-instagram-feed' ),
							value: attributes.count,
							onChange: function ( val ) {
								setAttributes( { count: val } );
							},
							min: 0,
							max: 50,
						} ),
						el( SelectControl, {
							label: __( 'Layout override', 'lumina-instagram-feed' ),
							value: attributes.layout,
							options: [
								{ label: __( 'Global default', 'lumina-instagram-feed' ), value: '' },
								{ label: 'Grid', value: 'grid' },
								{ label: 'Masonry', value: 'masonry' },
								{ label: 'Carousel', value: 'carousel' },
								{ label: 'Showcase', value: 'showcase' },
								{ label: 'Bento', value: 'bento' },
								{ label: 'Mosaic', value: 'mosaic' },
							],
							onChange: function ( val ) {
								setAttributes( { layout: val } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show caption', 'lumina-instagram-feed' ),
							checked: attributes.show_caption,
							onChange: function ( val ) {
								setAttributes( { show_caption: val } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show date', 'lumina-instagram-feed' ),
							checked: attributes.show_date,
							onChange: function ( val ) {
								setAttributes( { show_date: val } );
							},
						} ),
					)
				),
				el(
					Placeholder,
					{
						icon: 'instagram',
						label: __( 'Lumina Instagram Feed', 'lumina-instagram-feed' ),
						instructions: __(
							'This block renders your Instagram feed on the front end. Configure API credentials and design in Lumina Instagram settings.',
							'lumina-instagram-feed'
						),
					}
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
);
