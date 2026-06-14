<?php
/**
 * Metadata Processing Module
 * Optimizes titles automatically based on incoming lecturer structure guidelines
 */
class TitleOptimizer {
    
    public static function cleanAndOptimize($rawTitle, $subjectCode = "BITP3353") {
        // Trims white space and strips accidental double-extensions or jumbled characters
        $cleanTitle = trim(preg_replace('/[^A-Za-z0-9 ]/', '', $rawTitle));
        
        // Capitalizes words beautifully for the video card dashboard layout display
        $cleanTitle = ucwords(strtolower($cleanTitle));
        
        // Fallback check: If title is empty, generate an architectural default
        if (empty($cleanTitle)) {
            $cleanTitle = "Untitled Portfolio Project";
        }
        
        return $cleanTitle;
    }
}
?>