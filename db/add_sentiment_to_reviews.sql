-- Add sentiment analysis columns to reviews table
ALTER TABLE reviews 
ADD COLUMN sentiment_label ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral' AFTER review_text,
ADD COLUMN sentiment_score DECIMAL(4,3) DEFAULT 0.000 AFTER sentiment_label,
ADD INDEX idx_sentiment_label (sentiment_label),
ADD INDEX idx_sentiment_score (sentiment_score);

