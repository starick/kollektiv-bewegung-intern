export interface Protocol {
  id: number;
  date: string;
  description: string | null;
  topics: string[];
  participants: string[];
  createdAt: Date;
  updatedAt: Date;
  uploader?: { id: number; name: string } | null;
  editor?: { id: number; name: string } | null;
}
