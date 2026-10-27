<style>
    @page {
        size: A4;
        margin: 18mm 20mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: #000;
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.35;
    }

    .letterhead {
        display: table;
        width: 100%;
        margin-bottom: 4px;
    }

    .letterhead-logo,
    .letterhead-content {
        display: table-cell;
        vertical-align: middle;
    }

    .letterhead-logo {
        width: 82px;
        padding-right: 10px;
    }

    .letterhead-logo img {
        display: block;
        width: 72px;
        height: 72px;
    }

    .letterhead-content {
        text-align: center;
    }

    .letterhead-content p {
        margin: 0;
        line-height: 1.14;
    }

    .ministry {
        font-size: 10pt;
    }

    .university {
        font-size: 14pt;
        font-weight: bold;
    }

    .faculty {
        font-size: 12pt;
        font-weight: bold;
    }

    .address {
        margin-top: 5px !important;
        font-size: 9.5pt;
    }

    .letterhead-rule {
        margin: 6px 0 19px;
        border: 0;
        border-top: 3px double #000;
    }

    .reference {
        display: table;
        width: 100%;
        margin-bottom: 17px;
    }

    .reference-left,
    .reference-date {
        display: table-cell;
        vertical-align: top;
    }

    .reference-left {
        width: 71%;
    }

    .reference-date {
        width: 29%;
        text-align: right;
        white-space: nowrap;
    }

    .reference-table,
    .student-table {
        border-collapse: collapse;
    }

    .reference-table td {
        padding: 0 0 2px;
        vertical-align: top;
    }

    .reference-label {
        width: 92px;
    }

    .reference-colon {
        width: 15px;
    }

    .recipient {
        margin: 0 0 18px;
        line-height: 1.32;
    }

    .paragraph {
        margin: 0 0 13px;
        text-align: justify;
        text-indent: 28px;
        line-height: 1.48;
    }

    .student-table {
        width: 92%;
        margin: 4px 0 14px 28px;
    }

    .student-table td {
        padding: 0 0 3px;
        vertical-align: top;
        line-height: 1.28;
    }

    .student-label {
        width: 135px;
    }

    .student-colon {
        width: 16px;
    }

    .signature-row {
        display: table;
        width: 100%;
        margin-top: 30px;
    }

    .signature-spacer,
    .signature {
        display: table-cell;
        vertical-align: top;
    }

    .signature-spacer {
        width: 56%;
    }

    .signature {
        width: 44%;
    }

    .signature p {
        margin: 0;
        line-height: 1.38;
    }

    .signature-space {
        height: 68px;
    }

    .signature-name {
        font-weight: bold;
        text-decoration: underline;
    }
</style>
