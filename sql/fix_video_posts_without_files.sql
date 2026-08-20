-- Find video posts without actual video files
-- This helps identify posts that need to be re-uploaded

SELECT 
    id,
    title,
    slug,
    content_type,
    video_file,
    video_thumbnail,
    status,
    created_at
FROM posts
WHERE content_type = 'video' 
  AND (video_file IS NULL OR video_file = '');

-- To fix: Either re-upload the video in admin panel, or change content_type back to 'typed'
-- Option 1: Edit the post in admin and re-upload the video
-- Option 2: Change to typed content (uncomment below):

-- UPDATE posts 
-- SET content_type = 'typed' 
-- WHERE content_type = 'video' 
--   AND (video_file IS NULL OR video_file = '');
