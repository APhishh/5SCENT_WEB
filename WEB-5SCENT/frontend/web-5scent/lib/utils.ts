import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatCurrency(value: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}

/**
 * Rounds a rating to the nearest 0 or 0.5
 * Examples: 3.5-3.99 rounds to 3.5, 3.0-3.49 rounds to 3.0
 */
export function roundRating(rating: number): number {
  return Math.round(rating * 2) / 2
}
