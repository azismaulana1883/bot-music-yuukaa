import ytdl from '@distube/ytdl-core';

try {
    const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
    console.log("Fetching video info using @distube/ytdl-core for:", url);
    const info = await ytdl.getInfo(url);
    console.log("Title:", info.videoDetails.title);
    const formats = ytdl.filterFormats(info.formats, 'audioonly');
    console.log("Audio formats count:", formats.length);
    if (formats.length > 0) {
        console.log("First audio format URL exists? ", !!formats[0].url);
        console.log("Success!");
    } else {
        console.log("No audio formats found.");
    }
} catch (error) {
    console.error("Error occurred:", error);
}
