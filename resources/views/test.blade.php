<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-8">
    <h1 class="text-2xl text-yellow-400 mb-4">🎵 Simple Music Test</h1>
    
    <div class="bg-gray-800 rounded-lg p-4 mb-4">
        <h2 class="text-lg mb-2">Test Results:</h2>
        <div id="test-results" class="space-y-2">
            <div class="text-green-400">✅ HTML is working</div>
            <div id="js-test" class="text-red-400">❌ JavaScript not loaded</div>
            <div id="api-test" class="text-red-400">❌ API not tested</div>
        </div>
    </div>
    
    <div class="bg-gray-800 rounded-lg p-4">
        <h2 class="text-lg mb-2">Tracks:</h2>
        <div id="tracks" class="text-gray-400">Loading...</div>
    </div>
    
    <script>
        // Test JavaScript
        document.getElementById('js-test').innerHTML = '<span class="text-green-400">✅ JavaScript is working</span>';
        
        // Test API
        fetch('/api/music/tracks')
            .then(response => response.json())
            .then(data => {
                document.getElementById('api-test').innerHTML = '<span class="text-green-400">✅ API is working - ' + (data.data ? data.data.length : 0) + ' tracks found</span>';
                
                const tracks = data.data || [];
                let html = '';
                if (tracks.length === 0) {
                    html = '<div class="text-yellow-400">No tracks found</div>';
                } else {
                    tracks.forEach(track => {
                        html += '<div class="p-2 border-b border-gray-600">';
                        html += '<div class="text-white">' + (track.title || 'No title') + '</div>';
                        html += '<div class="text-gray-400 text-sm">' + (track.artist || 'No artist') + '</div>';
                        html += '</div>';
                    });
                }
                document.getElementById('tracks').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('api-test').innerHTML = '<span class="text-red-400">❌ API failed: ' + error.message + '</span>';
                document.getElementById('tracks').innerHTML = '<div class="text-red-400">Error: ' + error.message + '</div>';
            });
    </script>
</body>
</html>
