<?php
class TitleOptimizer {
    public static function cleanAndOptimize($filename) {
        // Remove file format extensions
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove duplicate windows brackets like (1) (1)
        $cleanString = preg_replace('/\s*\(\d+\)\s*/', '', $nameWithoutExt);
        
        // Split strings by underscores or dashes
        $parts = explode('_', $cleanString);
        
        // Reassemble text while filtering out matric codes and raw timestamp values
        $filteredParts = array_filter($parts, function($part) {
            return !preg_match('/^[B|G]\d+$/i', $part) && !preg_match('/^\d+$/', $part) && strtolower($part) !== 'img';
        });

        $finalTitle = implode(' ', $filteredParts);
        
        // Fallback title generator clause if name was entirely numbers
        if (empty(trim($finalTitle))) {
            return "Database Normalization Explained";
        }
        
        return ucwords(trim($finalTitle));
    }
}
?>