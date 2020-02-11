const path = require('path');

module.exports = {
    output: {
        filename: '[name].js?id=[hash]',
        chunkFilename: 'js/chunks/[name].js?id=[chunkhash]',
        publicPath: '/',
    },
};
