// CKEditor init with mentions as "merge fields"
// const vars = (@json($vars)).filter(Boolean);
let editorHeader = false;

function mkEditor(selector) {
    return CKEditorBuild.create(document.querySelector(selector), {
        toolbar: {
            items: [
                'fullscreen','heading', 'style',
                '|', 'bold', 'italic', 'link', 'alignment',
                '|', 'horizontalLine', 'bulletedList', 'numberedList', 'insertTable', 'insertTableLayout',
                '|', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'Underline',
                '|', 'undo', 'redo', 'pageBreak', 'ImageUpload'
            ]
        },
        mention: {
            // (CKEditor 5 mendukung custom marker & limit dropdown)
            feeds: [
                {
                    marker: "@",
                    feed: (queryText) => {
                        const q = (queryText || "").toLowerCase();
                        return vars
                            .filter((v) => v.toLowerCase().startsWith(q))
                            .map((v) => `@${v}`);
                    },
                    minimumCharacters: 0,
                    dropdownLimit: 50,
                },
            ],
        },
        image: {
            upload: { types: [ 'jpeg', 'jpg', 'png', 'gif', 'webp' ] },
            toolbar: [
                'imageStyle:inline','imageStyle:block','imageStyle:side',
                '|','toggleImageCaption','imageTextAlternative',
                '|','resizeImage'
            ],
            resizeUnit: 'px',
            // (Opsional) tombol preset di toolbar
            resizeOptions: [
                { name: 'resizeImage:original', label: 'Original', value: null },
                { name: 'resizeImage:custom',   label: 'Custom',  value: 'custom' },
                { name: 'resizeImage:100',      label: '100 px',  value: 100 },
                { name: 'resizeImage:300',      label: '300 px',  value: 300 },
                { name: 'resizeImage:600',      label: '600 px',  value: 600 }
            ],
        },
        // image: {
        //     toolbar: [
        //     'imageTextAlternative','imageStyle:alignLeft','imageStyle:full','imageStyle:alignRight',
        //     ],
        //     styles: [ 'full', 'alignLeft', 'alignRight' ]
        // },
        // simpleUpload: {
        //   uploadUrl: `${base_url}/api/v1/document/upload_image`,
        //   headers: {
        //     'Authorization': `Bearer ${bearer}` ,
        //     'Accept': 'application/json',           // penting agar Laravel balas JSON, bukan redirect HTML
        //     'X-Requested-With': 'XMLHttpRequest'   // membantu Laravel menganggap request AJAX
        //   }
        // },
        // Opsi font
        fontFamily: {
        options: [
            'default',
            'Arial, Helvetica, sans-serif',
            'Times New Roman, Times, serif',
            'Courier New, Courier, monospace',
            'Tahoma, Geneva, sans-serif',
            'Georgia, serif'
        ],
        supportAllValues: true
        },
        fontSize: {
            options: [ '8', '10', '12', '14', '16', '18', '24', '32', '48' ],
            supportAllValues: true
        },
        fontColor: { colorPicker: { format: 'hex' } },
        fontBackgroundColor: { colorPicker: { format: 'hex' } }
    }).then((editor) => {
        editorHeader = editor;
    }).catch((error) => {
        console.error(error);
    });
}

Promise.all([mkEditor("#header_html")]);

// simpan variables[] dari input text
const varsInput = document.getElementById("varsInput");
const varsHidden = document.getElementById("varsHidden");
function syncVars() {
    const items = (varsInput.value || "")
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean);
    varsHidden.setAttribute("name", "variables"); // kirim sebagai array di Laravel
    varsHidden.value = JSON.stringify(items);
    vars = items;
}
varsInput.addEventListener("input", syncVars);


function simpanHeader(obj) {
    const nama_header = document.getElementById("nama_header");
    const header_html = editorHeader.getData();
    const used = [...extractImgSrcSet(header_html)];
    const header = document.getElementById("header").value;
    const footer = document.getElementById("footer").value;

    if (nama_header.value == "") {
        Swal.fire({
            icon: "warning",
            text: "Nama header tidak boleh kosong",
        });
        return;
    } else if (header_html == "") {
        Swal.fire({
            icon: "warning",
            text: "Header tidak boleh kosong",
        });
        return;
    }

    const formParams = new FormData();
    formParams.append("variables", vars);
    formParams.append("title", nama_header.value);
    formParams.append("content", convertHslToHex(header_html));
    formParams.append("jenis", type);
    formParams.append("used_images", JSON.stringify(used));
    formParams.append("header", header);
    formParams.append("footer", footer);

    let id = _data.doc_hash || '';

    spinner("show", $(obj));
    ajaxPost(`management/document/${id}`, formParams, (result) => {
        Swal.fire({
            icon: "success",
            text: result.data.msg,
        });
        spinner("hide", $(obj));
        // window.location.href = `${base_url}/management/document`;
    }, (error) => {
        spinner("hide", $(obj));
        console.error(error);
    });
}
