import YTDlpWrap from 'yt-dlp-wrap';
import fs from 'fs';
import https from 'https';

const ytdlpPath = './yt-dlp.exe';

async function test() {
    try {
        if (!fs.existsSync(ytdlpPath)) {
            console.log("Downloading yt-dlp binary...");
            await YTDlpWrap.default.downloadFromGithub(ytdlpPath);
            console.log("Download complete!");
        } else {
            console.log("yt-dlp binary already exists.");
        }

        const ytDlpWrap = new YTDlpWrap.default(ytdlpPath);
        const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
        console.log("Extracting audio URL for:", url);
        
        const stdout = await ytDlpWrap.execPromise([
            url,
            '-f', 'bestaudio',
            '--get-url'
        ]);
        
        const audioUrl = stdout.trim();
        console.log("Extracted audio URL:", audioUrl.substring(0, 100) + '...');
        
        console.log("Testing URL request...");
        https.get(audioUrl, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            }
        }, (res) => {
            console.log("Status code:", res.statusCode);
            if (res.statusCode === 200 || res.statusCode === 206) {
                console.log("Success! Stream is fully accessible!");
            } else {
                console.log("Failed with status:", res.statusCode);
            }
        }).on('error', (err) => {
            console.error("HTTP request error:", err);
        });

    } catch (err) {
        console.error("Error occurred:", err);
    }
}

test();
