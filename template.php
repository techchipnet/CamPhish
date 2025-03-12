<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Code Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        input {
            width: 60%;
            padding: 10px;
            margin: 10px;
        }
        button {
            padding: 10px 20px;
            cursor: pointer;
        }
        #code img {
            margin-top: 20px;
            width: 300px;
        }
    </style>
</head>
<body>
    <h1>Spotify Code Generator</h1>
    <p>Enter a Spotify song, playlist, or album link:</p>
    <input type="text" id="spotifyLink" placeholder="Paste Spotify link here...">
    <button onclick="generateCode()">Generate Code</button>
    <div id="code"></div>
    
    <script>
        function generateCode() {
            let link = document.getElementById('spotifyLink').value.trim();
            if (link.includes("spotify.com")) {
                let encodedLink = encodeURIComponent(link);
                let imgUrl = `https://scannables.scdn.co/uri/plain/png/000000/white/640/${encodedLink}`;
                document.getElementById('code').innerHTML = `<img src="${imgUrl}" alt="Spotify Code">`;
            } else {
                alert("Please enter a valid Spotify link.");
            }
        }
    </script>
</body>
</html>
