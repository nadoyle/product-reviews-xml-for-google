/* File: assets/js/admin.js */
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('prxml_copy_btn');
    var text = document.getElementById('prxml_url_text');
    
    if(btn && text) {
        btn.addEventListener('click', function() {
            var range = document.createRange();
            range.selectNode(text);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            
            try {
                document.execCommand('copy');
                var originalText = btn.textContent;
                // We use the localized object 'prxml_vars' passed from PHP
                btn.textContent = prxml_vars.copied_text;
                setTimeout(function(){
                    btn.textContent = originalText;
                }, 2000);
            } catch(err) {
                console.error('Unable to copy');
            }
            window.getSelection().removeAllRanges();
        });
    }
});