/**
 * BMLT Stats Block
 *
 * Gutenberg block for displaying BMLT statistics
 */

(function(wp) {
    'use strict';

    var registerBlockType = wp.blocks.registerBlockType;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var createElement = wp.element.createElement;
    var __ = wp.i18n.__;

    // Get preview data from localized script
    var previewData = window.blstBlockData ? window.blstBlockData.previewData : {};

    /**
     * Format large numbers for display
     */
    function formatNumber(num) {
        if (!num) return '0';
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }

    /**
     * Preview Card Component
     */
    function PreviewCard(props) {
        return createElement(
            'div',
            { className: 'blst-preview-card' },
            createElement('span', { className: 'blst-preview-number' }, props.number),
            createElement('span', { className: 'blst-preview-label' }, props.label)
        );
    }

    /**
     * Block Edit Component
     */
    function EditBlock(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;

        var blockProps = useBlockProps({
            className: 'blst-block-preview blst-theme-' + attributes.theme
        });

        // Display type options
        var displayOptions = [
            { label: __('Full Stats Display', 'bmlt-enabled-stats'), value: 'full' },
            { label: __('Summary Cards Only', 'bmlt-enabled-stats'), value: 'summary' },
            { label: __('GitHub Stats Only', 'bmlt-enabled-stats'), value: 'github' },
            { label: __('WordPress Plugins Only', 'bmlt-enabled-stats'), value: 'wordpress' },
            { label: __('BMLT Meetings Only', 'bmlt-enabled-stats'), value: 'meetings' }
        ];

        // Theme options
        var themeOptions = [
            { label: __('Default', 'bmlt-enabled-stats'), value: 'default' },
            { label: __('Dark', 'bmlt-enabled-stats'), value: 'dark' },
            { label: __('Compact', 'bmlt-enabled-stats'), value: 'compact' }
        ];

        // Render preview content based on display type
        var renderPreview = function() {
            var type = attributes.displayType;

            if (type === 'summary' || type === 'full') {
                return createElement(
                    'div',
                    { className: 'blst-preview-grid' },
                    createElement(PreviewCard, {
                        number: formatNumber(previewData.total_meetings),
                        label: __('Meetings', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: previewData.root_servers,
                        label: __('Root Servers', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: previewData.total_github_stars,
                        label: __('GitHub Stars', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: formatNumber(previewData.total_downloads),
                        label: __('Downloads', 'bmlt-enabled-stats')
                    })
                );
            }

            if (type === 'github') {
                return createElement(
                    'div',
                    { className: 'blst-preview-grid' },
                    createElement(PreviewCard, {
                        number: previewData.total_repos,
                        label: __('Repositories', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: previewData.total_github_stars,
                        label: __('Stars', 'bmlt-enabled-stats')
                    })
                );
            }

            if (type === 'wordpress') {
                return createElement(
                    'div',
                    { className: 'blst-preview-grid' },
                    createElement(PreviewCard, {
                        number: formatNumber(previewData.total_downloads),
                        label: __('Downloads', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: formatNumber(previewData.total_active_installs) + '+',
                        label: __('Active Installs', 'bmlt-enabled-stats')
                    })
                );
            }

            if (type === 'meetings') {
                return createElement(
                    'div',
                    { className: 'blst-preview-grid' },
                    createElement(PreviewCard, {
                        number: formatNumber(previewData.total_meetings),
                        label: __('Meetings', 'bmlt-enabled-stats')
                    }),
                    createElement(PreviewCard, {
                        number: previewData.root_servers,
                        label: __('Root Servers', 'bmlt-enabled-stats')
                    })
                );
            }

            return null;
        };

        return createElement(
            'div',
            blockProps,
            // Inspector Controls (sidebar)
            createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    { title: __('Display Settings', 'bmlt-enabled-stats'), initialOpen: true },
                    createElement(SelectControl, {
                        label: __('Display Type', 'bmlt-enabled-stats'),
                        value: attributes.displayType,
                        options: displayOptions,
                        onChange: function(value) {
                            setAttributes({ displayType: value });
                        }
                    }),
                    createElement(SelectControl, {
                        label: __('Theme', 'bmlt-enabled-stats'),
                        value: attributes.theme,
                        options: themeOptions,
                        onChange: function(value) {
                            setAttributes({ theme: value });
                        }
                    })
                )
            ),
            // Block Preview
            createElement(
                'div',
                { className: 'blst-block-content' },
                createElement(
                    'div',
                    { className: 'blst-block-header' },
                    createElement(
                        'span',
                        { className: 'blst-block-icon' },
                        createElement('svg', {
                            viewBox: '0 0 24 24',
                            fill: 'currentColor',
                            width: 24,
                            height: 24
                        },
                            createElement('path', {
                                d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z'
                            })
                        )
                    ),
                    createElement('span', { className: 'blst-block-title' }, __('BMLT Stats', 'bmlt-enabled-stats')),
                    createElement(
                        'span',
                        { className: 'blst-block-type' },
                        displayOptions.find(function(opt) { return opt.value === attributes.displayType; }).label
                    )
                ),
                renderPreview()
            )
        );
    }

    // Register the block
    registerBlockType('bmlt-enabled-stats/stats-display', {
        edit: EditBlock,
        save: function() {
            // Server-side rendering
            return null;
        }
    });

})(window.wp);
