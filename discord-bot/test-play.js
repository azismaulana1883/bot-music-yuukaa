import play from 'play-dl';

try {
    const url = "https://www.youtube.com/watch?v=DL7z19KoFAM";
    const info = await play.video_info(url);
    
    // Filter formats to only include those with valid URLs
    info.format = info.format.filter(f => f.url);
    console.log("Filtered format count:", info.format.length);
    
    console.log("Attempting stream_from_info on filtered formats...");
    const stream = await play.stream_from_info(info);
    console.log("Stream success! Stream type:", stream.type);
} catch (error) {
    console.error("Error:", error);
}
