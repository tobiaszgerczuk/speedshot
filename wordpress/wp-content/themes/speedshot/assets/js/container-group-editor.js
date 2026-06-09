(() => {
  const wp = window.wp || {};
  const blocks = wp.blocks || {};
  const blockEditor = wp.blockEditor || {};
  const element = wp.element || {};
  const components = wp.components || {};

  const registerBlockType = blocks.registerBlockType;
  const { InspectorControls, InnerBlocks, useBlockProps } = blockEditor;
  const { createElement: el } = element;
  const { PanelBody, SelectControl } = components;
  const isPolish = (document.documentElement.lang || '').toLowerCase().startsWith('pl');

  if (!registerBlockType || !useBlockProps || !InnerBlocks || !el || !PanelBody || !SelectControl) {
    return;
  }

  const LABELS = isPolish
    ? {
        title: 'Grupa kontenera',
        description: 'Blok opakowujący z zagnieżdżonymi blokami i wyborem szerokości kontenera.',
        panelTitle: 'Ustawienia kontenera',
        fieldLabel: 'Szerokość kontenera',
        spacingPanelTitle: 'Paddingi i marginesy',
        marginTop: 'Margines górny',
        marginBottom: 'Margines dolny',
        paddingTop: 'Padding górny',
        paddingBottom: 'Padding dolny',
        default: 'Domyślna',
        wide: 'Szeroka',
        medium: 'Średnia',
        narrow: 'Wąska',
        full: 'Pełna szerokość',
        none: 'Brak',
      }
    : {
        title: 'Container Group',
        description: 'Wrapper block with nested blocks and configurable container width.',
        panelTitle: 'Container settings',
        fieldLabel: 'Container width',
        spacingPanelTitle: 'Padding and margins',
        marginTop: 'Margin top',
        marginBottom: 'Margin bottom',
        paddingTop: 'Padding top',
        paddingBottom: 'Padding bottom',
        default: 'Default',
        wide: 'Wide',
        medium: 'Medium',
        narrow: 'Narrow',
        full: 'Full',
        none: 'None',
      };

  const CONTAINER_WIDTH_OPTIONS = [
    { label: LABELS.default, value: 'default' },
    { label: LABELS.wide, value: 'wide' },
    { label: LABELS.medium, value: 'medium' },
    { label: LABELS.narrow, value: 'narrow' },
    { label: LABELS.full, value: 'full' },
  ];

  const SPACING_OPTIONS = [
    { label: LABELS.none, value: 'none' },
    { label: 'XS', value: 'xs' },
    { label: 'SM', value: 'sm' },
    { label: 'MD', value: 'md' },
    { label: 'LG', value: 'lg' },
    { label: 'XL', value: 'xl' },
  ];

  registerBlockType('acf/container-group', {
    title: LABELS.title,
    description: LABELS.description,
    category: 'wco-blocks',
    icon: 'align-wide',
    supports: {
      align: ['full', 'wide'],
    },
    attributes: {
      containerWidth: {
        type: 'string',
        default: 'default',
      },
      sectionGapTop: {
        type: 'string',
        default: 'none',
      },
      sectionGapBottom: {
        type: 'string',
        default: 'none',
      },
      sectionSpaceTop: {
        type: 'string',
        default: 'md',
      },
      sectionSpaceBottom: {
        type: 'string',
        default: 'md',
      },
    },
    edit({ attributes, setAttributes }) {
      const {
        containerWidth,
        sectionGapTop,
        sectionGapBottom,
        sectionSpaceTop,
        sectionSpaceBottom,
      } = attributes;
      const blockProps = useBlockProps({ className: 'block-container-group' });

      return el(
        'div',
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            {
              title: LABELS.panelTitle,
              initialOpen: true,
            },
            el(SelectControl, {
              label: LABELS.fieldLabel,
              value: containerWidth,
              options: CONTAINER_WIDTH_OPTIONS,
              onChange(value) {
                setAttributes({ containerWidth: value });
              },
            })
          ),
          el(
            PanelBody,
            {
              title: LABELS.spacingPanelTitle,
              initialOpen: false,
            },
            el(SelectControl, {
              label: LABELS.marginTop,
              value: sectionGapTop,
              options: SPACING_OPTIONS,
              onChange(value) {
                setAttributes({ sectionGapTop: value });
              },
            }),
            el(SelectControl, {
              label: LABELS.marginBottom,
              value: sectionGapBottom,
              options: SPACING_OPTIONS,
              onChange(value) {
                setAttributes({ sectionGapBottom: value });
              },
            }),
            el(SelectControl, {
              label: LABELS.paddingTop,
              value: sectionSpaceTop,
              options: SPACING_OPTIONS,
              onChange(value) {
                setAttributes({ sectionSpaceTop: value });
              },
            }),
            el(SelectControl, {
              label: LABELS.paddingBottom,
              value: sectionSpaceBottom,
              options: SPACING_OPTIONS,
              onChange(value) {
                setAttributes({ sectionSpaceBottom: value });
              },
            })
          )
        ),
        el('div', blockProps, el(InnerBlocks, { templateLock: false }))
      );
    },
    save() {
      return el(InnerBlocks.Content);
    },
  });
})();
