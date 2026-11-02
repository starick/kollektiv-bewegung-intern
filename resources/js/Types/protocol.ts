export interface Protocol {
  id: number;
  date: string;
  description: string | null;
  createdAt: Date;
  updatedAt: Date;
  uploader?: {
    id: number;
    name: string;
    email: string;
  } | null;
  editor?: {
    id: number;
    name: string;
    email: string;
  } | null;
}
