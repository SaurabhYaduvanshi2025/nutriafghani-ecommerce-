document.addEventListener('DOMContentLoaded', function () {
    var editorElement = document.getElementById('blog-editor');
    var contentField = document.getElementById('content');
    var blogForm = document.getElementById('blog-form');

    if (!editorElement || !contentField || !blogForm || typeof Quill === 'undefined') {
        return;
    }

    var FontAttributor = Quill.import('attributors/class/font');
    Quill.register(FontAttributor, true);

    var SizeAttributor = Quill.import('attributors/class/size');
    SizeAttributor.whitelist = ['small', 'normal', 'large', 'huge'];
    Quill.register(SizeAttributor, true);

    var modules = {
        toolbar: {
            container: [
                [{ header: [1, 2, 3, false] }],
                [{ size: ['small', 'normal', 'large', 'huge'] }],
                ['bold', 'italic'],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                ['image'],
                ['clean']
            ],
            handlers: {
                image: imageHandler
            }
        }
    };

    if (typeof ImageResize !== 'undefined') {
        modules.imageResize = {
            displaySize: true
        };
    }

    var quill = new Quill(editorElement, {
        theme: 'snow',
        modules: modules
    });

    quill.root.innerHTML = contentField.value || '';

    blogForm.addEventListener('submit', function () {
        contentField.value = quill.root.innerHTML;
    });

    function imageHandler() {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/jpeg,image/png,image/webp,image/gif');
        input.click();

        input.onchange = function () {
            var file = input.files && input.files[0];
            if (!file) {
                return;
            }

            var formData = new FormData();
            formData.append('image', file);

            fetch('blog-upload.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        alert(data.message || 'Image upload failed.');
                        return;
                    }

                    var range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', data.url, 'user');
                    quill.setSelection(range.index + 1);
                })
                .catch(function () {
                    alert('Image upload failed. Please try again.');
                });
        };
    }
});
