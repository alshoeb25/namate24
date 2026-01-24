<?php

namespace App\Services;

use App\Models\FieldLabel;

class LabelService
{
    /**
     * Add labels to a model or array
     * 
     * @param mixed $data Model instance or array
     * @param array $fieldMappings Map of field names to their values
     * @return mixed
     */
    public function addLabels($data, array $fieldMappings = [])
    {
        // Default field mappings for requirements/enquiries
        if (empty($fieldMappings)) {
            $fieldMappings = [
                'service_type' => 'service_type',
                'budget_type' => 'budget_type',
                'gender_preference' => 'gender_preference',
                'availability' => 'availability',
                'status' => 'status',
                'lead_status' => 'lead_status',
                'tutor_location_preference' => 'tutor_location_preference',
            ];
        }

        $isArray = is_array($data);
        $item = $isArray ? (object)$data : $data;

        // Add single field labels
        foreach ($fieldMappings as $fieldName => $property) {
            if (isset($item->$property) && $item->$property) {
                $labelKey = $fieldName . '_label';
                $label = FieldLabel::getLabel($fieldName, $item->$property);
                
                if ($isArray) {
                    $data[$labelKey] = $label ?? $item->$property;
                } else {
                    $item->$labelKey = $label ?? $item->$property;
                }
            }
        }

        // Handle meeting_options array
        if (isset($item->meeting_options) && is_array($item->meeting_options)) {
            $labels = array_map(function ($option) {
                return FieldLabel::getLabel('meeting_options', $option) ?? $option;
            }, $item->meeting_options);
            
            if ($isArray) {
                $data['meeting_options_labels'] = $labels;
            } else {
                $item->meeting_options_labels = $labels;
            }
        }

        // Add budget display
        if (isset($item->budget) && $item->budget) {
            $budgetTypeLabel = isset($item->budget_type) 
                ? FieldLabel::getLabel('budget_type', $item->budget_type) 
                : '';
            $budgetDisplay = '₹' . number_format($item->budget, 0);
            if ($budgetTypeLabel) {
                $budgetDisplay .= ' ' . $budgetTypeLabel;
            }
            
            if ($isArray) {
                $data['budget_display'] = $budgetDisplay;
            } else {
                $item->budget_display = $budgetDisplay;
            }
        }

        // Add location display
        if (isset($item->location) || isset($item->city)) {
            $locationDisplay = $item->location ?? '';
            if (!$locationDisplay && isset($item->city)) {
                $locationDisplay = $item->city;
                if (isset($item->area) && $item->area) {
                    $locationDisplay .= ', ' . $item->area;
                }
            }
            
            if ($isArray) {
                $data['location_display'] = $locationDisplay;
            } else {
                $item->location_display = $locationDisplay;
            }
        }

        // Add subject name from subject relationship (single subject)
        if (!$isArray && method_exists($item, 'relationLoaded') && $item->relationLoaded('subject') && $item->subject) {
            $item->subject_name = $item->subject->name;
        }

        // Add subject names if subjects relationship exists (multiple subjects)
        if (!$isArray && method_exists($item, 'relationLoaded') && $item->relationLoaded('subjects') && $item->subjects) {
            $item->subject_names = $item->subjects->pluck('name')->toArray();
        }

        return $data;
    }

    /**
     * Get all labels for a specific field
     */
    public function getFieldLabels(string $fieldName): array
    {
        return FieldLabel::getFieldLabels($fieldName);
    }

    /**
     * Get a single label
     */
    public function getLabel(string $fieldName, string $fieldValue): ?string
    {
        return FieldLabel::getLabel($fieldName, $fieldValue);
    }
}
