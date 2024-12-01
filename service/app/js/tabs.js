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


    // Set initial active tab
    tabs[0].classList.add('active');
    contents[0].style.display = 'block';
});

/*
button.addEventListener('click', () => {
    const url = button.getAttribute('preview');
    const infos = button.getAttribute('infos');
    if (url) {
        // Clear previous content
        previewContent.innerHTML = '';
        
        // Display infos text
        const previewInfo = document.getElementById('preview-info');
        previewInfo.textContent = infos; // Update the text at the top of the "preview" div
        
        // Create and add iframe
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.width = '100%';
        iframe.style.border = '1px solid #ccc';
        iframe.style.height = '1200px'; // Use a tall fixed height for iframe
        iframe.style.display = 'block'; // Ensures no scrollbars on the iframe
        previewContent.appendChild(iframe);

        // Switch to Preview tab
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(content => content.style.display = 'none');
        
        document.querySelector('.tab-button[data-tab="preview"]').classList.add('active');
        previewTab.style.display = 'block';
    }
});
*/
