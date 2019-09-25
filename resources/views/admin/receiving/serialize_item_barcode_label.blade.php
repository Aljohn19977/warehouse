<!-- <div>{!! DNS1D::getBarcodeHTML("4445645656", "EAN13",2,60) !!}</div> -->
<!-- <style>
.page-break {
    page-break-after: always;
}
</style> -->

<!DOCTYPE html>
<html>
    <head>
        <title>Save and Print</title>
        <style>
            html, body, div, span, applet, object, iframe,
            h1, h2, h3, h4, h5, h6, p, blockquote, pre,
            a, abbr, acronym, address, big, cite, code,
            del, dfn, em, img, ins, kbd, q, s, samp,
            small, strike, strong, sub, sup, tt, var,
            b, u, i, center,
            dl, dt, dd, ol, ul, li,
            fieldset, form, label, legend,
            table, caption, tbody, tfoot, thead, tr, th, td,
            article, aside, canvas, details, embed, 
            figure, figcaption, footer, header, hgroup, 
            menu, nav, output, ruby, section, summary,
            time, mark, audio, video {
                margin: 0;
                padding: 0;
                border: 0;
                font-size: 100%;
                font: inherit;
                vertical-align: baseline;
            }
            /* HTML5 display-role reset for older browsers */
            article, aside, details, figcaption, figure, 
            footer, header, hgroup, menu, nav, section {
                display: block;
            }
            body {
                line-height: 1;
            }
            ol, ul {
                list-style: none;
            }
            blockquote, q {
                quotes: none;
            }
            blockquote:before, blockquote:after,
            q:before, q:after {
                content: '';
                content: none;
            }
            table {
                border-collapse: collapse;
                border-spacing: 0;
            }
            html, body {
                width: 152px;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            h1 {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-before: always;
            }
            .page-break-last {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div id="container">
            @foreach($barcodes as $barcode)
            <div>
                <div style="margin-top:9px; margin-left:8px;">{!! DNS1D::getBarcodeHTML($barcode['id'], "C39",1.4,40) !!}</div>
                <div style="margin-top:1px;">
                    <p style="font-size:11px; text-align:center;">{{ $barcode['serialize_item_id'] }}</p>
                    <p style="font-size:12px; text-align:center; margin-top:1px;">{{ $barcode['item_name'] }}</p>
                    <p style="font-size:11px; text-align:center; margin-top:2px;">{{ $barcode['expiration_date'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </body>
</html>