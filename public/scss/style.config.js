const AutoPrefixerPlugin = require('autoprefixer');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const CssNanoPlugin = require('cssnano');
const Path = require('path');

module.exports = [
    {
        target: 'web',
        entry: './style.scss',
        devtool: 'source-map',
        resolve: {
            extensions: [ '.scss' ]
        },
        plugins: [
            new FixStyleOnlyEntriesPlugin(),
            new MiniCssExtractPlugin({
                filename: 'style.css'
            })
        ],
        module: {
            rules: [
                {
                    test: /\.scss$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                url: false
                            }
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: {
                                    plugins: [
                                        AutoPrefixerPlugin(),
                                        CssNanoPlugin()
                                    ]
                                }
                            }
                        },
                        {
                            loader: 'sass-loader'
                        }
                    ]
                }
            ]
        },
        output: {
            path: Path.resolve('../css')
        }
    }
];
