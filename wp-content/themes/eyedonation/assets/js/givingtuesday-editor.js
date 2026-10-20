(function (blocks, element, editor, server) {
    blocks.registerBlockType('eyedonation/givingtuesday-slideshow', {
        title: 'Giving Tuesday slideshow', icon: 'images-alt2', category: 'media',
        description: 'Four campaign slides with separate desktop and mobile artwork.',
        edit: function () { return element.createElement('div', editor.useBlockProps(), element.createElement(server.default || server, {block: 'eyedonation/givingtuesday-slideshow'})); },
        save: function () { return null; }
    });
})(wp.blocks, wp.element, wp.blockEditor, wp.serverSideRender);
