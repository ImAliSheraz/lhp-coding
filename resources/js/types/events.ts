export interface EventVisual {
    id: string;
    title: string;
    description: string;
    type: string;
    status: string;
    starts_at: number;
    ends_at: number;
    location: {
        label: string;
        city: string;
        lat: number | null;
        lng: number | null;
    };
    images: string[];
    attendee_count?: number;
}

export interface CityOption {
    key: string;
    name: string;
}

export interface VisualFilters {
    from: string;
    to: string;
    city: string;
}
