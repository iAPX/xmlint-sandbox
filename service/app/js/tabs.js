document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');
    const previewButtons = document.querySelectorAll('.preview-button');
    const sourceButtons = document.querySelectorAll('.source-button');
    const previewTab = document.querySelector('#preview');
    const previewContent = document.getElementById('preview-content');

    // Tab switching logic
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(content => content.style.display = 'none');
            
            tab.classList.add('active');
            const target = document.getElementById(tab.getAttribute('data-tab'));
            target.style.display = 'block';
        });
    });

    // Preview button logic
    previewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const url = button.getAttribute('preview');
            const infos = button.getAttribute('infos');
            if (url) {
                // Display infos text
                const previewInfo = document.getElementById('preview-info');
                previewInfo.textContent = infos; // Update the text at the top of the "preview" div

                // Clear previous content
                previewContent.innerHTML = '';

                // Create and add iframe
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.width = '100%';
                iframe.height = '1200px';
                iframe.style.border = '1px solid #ccc';
                previewContent.appendChild(iframe);

                // Switch to Preview tab
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(content => content.style.display = 'none');
                
                document.querySelector('.tab-button[data-tab="preview"]').classList.add('active');
                previewTab.style.display = 'block';
            }
        });
    });

    // Source button logic
    sourceButtons.forEach(button => {
        button.addEventListener('click', () => {
            const url = button.getAttribute('preview');
            const infos = button.getAttribute('infos');
            if (url) {
                // Display infos text
                const previewInfo = document.getElementById('preview-info');
                previewInfo.textContent = infos; // Update the text at the top of the "preview" div

                // Clear previous content
                previewContent.innerHTML = '';
                
                fetch(url)
                    .then(response => response.text())
                    .then(data => {
                        const iframe = document.createElement('iframe');
                        document.getElementById('preview-content').innerHTML = ''; // Clear previous content
                        iframe.style.width = '100%';
                        iframe.style.height = '1200px';
                        iframe.style.border = '1px solid #ccc';
                        document.getElementById('preview-content').appendChild(iframe);

                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write('<pre>' + data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')+ '</pre>');
                        iframeDoc.close();
                    });
                
                // Switch to Preview tab
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(content => content.style.display = 'none');
                
                document.querySelector('.tab-button[data-tab="preview"]').classList.add('active');
                previewTab.style.display = 'block';
            }
        });
    });

    // Delete button logic
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            const listItem = this.closest('.list-item'); // Get the parent item
            const filename = listItem.getAttribute('data-filename'); // Get the filename
    
            // Show a confirmation popup
            const confirmed = confirm(`Etes-vous sur de vouloir effacer le fichier "${filename}"?`);
            if (confirmed) {
                // Create form data
                const formData = new FormData();
                formData.append('filename', filename);
    
                // Call the REST API to delete the content
                fetch('/app/delete.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (response.ok) {
                            alert(`"${filename}" a ete efface.`);
                            location.reload(); // Refresh the page after successful deletion
                        } else {
                            alert(`Erreur dans l'effacement de "${filename}".`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the content.');
                    });
            }
        });
    });

    // Set initial active tab
    tabs[0].classList.add('active');
    contents[0].style.display = 'block';
});
