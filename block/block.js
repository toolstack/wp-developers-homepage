( function ( blocks, element, serverSideRender, blockEditor ) {
    var el = element.createElement,
        registerBlockType = blocks.registerBlockType,
        ServerSideRender = serverSideRender,
        useBlockProps = blockEditor.useBlockProps;

    registerBlockType( 'wp-developers-homepage/tickets-block', {
        apiVersion: 2,
        title: 'Tickets Block',
        icon: 'hammer',
        category: 'wp-developers-homepage',

        edit: function ( props ) {
            var blockProps = useBlockProps();
            return el(
                'div',
                blockProps,
                el( ServerSideRender, {
                    block: 'wp-developers-homepage/tickets-block',
                    attributes: props.attributes,
                } )
            );
        },
    } );

    registerBlockType( 'wp-developers-homepage/stats-block', {
        apiVersion: 2,
        title: 'Stats Block',
        icon: 'hammer',
        category: 'wp-developers-homepage',

        edit: function ( props ) {
            var blockProps = useBlockProps();
            return el(
                'div',
                blockProps,
                el( ServerSideRender, {
                    block: 'wp-developers-homepage/stats-block',
                    attributes: props.attributes,
                } )
            );
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.serverSideRender,
    window.wp.blockEditor
);