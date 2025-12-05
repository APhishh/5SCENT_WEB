'use client';

import { useState, useEffect } from 'react';
import AdminLayout from '@/components/AdminLayout';
import api from '@/lib/api';
import { useToast } from '@/contexts/ToastContext';
import {
  XMarkIcon,
} from '@heroicons/react/24/outline';
import { FaStar, FaRegStar, FaRegStarHalfStroke } from 'react-icons/fa6';
import { FiEye, FiEyeOff, FiTrash2 } from 'react-icons/fi';

interface Review {
  rating_id: number;
  user_id: number;
  product_id: number;
  order_id: number;
  stars: number;
  comment: string;
  created_at?: string;
  user?: {
    user_id: number;
    name: string;
    email: string;
  };
  product?: {
    product_id: number;
    name: string;
  };
  is_visible?: boolean;
}

interface ReviewsData {
  reviews: Review[];
  total: number;
}

export default function ReviewsPage() {
  const { showToast } = useToast();
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState<string>(
    new Date().toISOString().split('T')[0]
  );
  const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);

  // Modal states
  const [showDetailsModal, setShowDetailsModal] = useState(false);
  const [selectedReview, setSelectedReview] = useState<Review | null>(null);

  useEffect(() => {
    fetchReviews();
  }, []);

  const fetchReviews = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/reviews');
      const data: ReviewsData = response.data;
      setReviews(Array.isArray(data) ? data : data.reviews || []);
    } catch (error) {
      console.error('Error fetching reviews:', error);
      showToast('Failed to load reviews', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleViewDetails = (review: Review) => {
    setSelectedReview(review);
    setShowDetailsModal(true);
  };

  const handleToggleVisibility = async (rating_id: number, currentVisibility: boolean) => {
    try {
      await api.put(`/admin/reviews/${rating_id}/visibility`, {
        is_visible: !currentVisibility,
      });
      setReviews(
        reviews.map((r) =>
          r.rating_id === rating_id ? { ...r, is_visible: !currentVisibility } : r
        )
      );
      showToast(
        `Review ${!currentVisibility ? 'shown' : 'hidden'} successfully`,
        'success'
      );
    } catch (error) {
      console.error('Error toggling visibility:', error);
      showToast('Failed to update review visibility', 'error');
    }
  };

  const handleDeleteReview = async (rating_id: number) => {
    try {
      await api.delete(`/admin/reviews/${rating_id}`);
      setReviews(reviews.filter((r) => r.rating_id !== rating_id));
      setDeleteConfirm(null);
      showToast('Review deleted successfully', 'success');
    } catch (error) {
      console.error('Error deleting review:', error);
      showToast('Failed to delete review', 'error');
    }
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toISOString().split('T')[0];
  };

  const truncateComment = (comment: string, length: number = 60) => {
    return comment.length > length ? comment.substring(0, length) + '...' : comment;
  };

  const renderStars = (rating: number) => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
      if (i <= rating) {
        stars.push(
          <FaStar
            key={i}
            size={16}
            className="text-black fill-black"
          />
        );
      } else if (i - rating === 0.5) {
        stars.push(
          <FaRegStarHalfStroke
            key={i}
            size={16}
            className="text-black fill-black"
          />
        );
      } else {
        stars.push(
          <FaRegStar
            key={i}
            size={16}
            className="text-black"
          />
        );
      }
    }
    return <div className="flex items-center gap-1">{stars}</div>;
  };

  return (
    <AdminLayout>
      <div className="flex flex-col gap-6 p-6">
        {/* Reviews Table Card */}
        <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Loading reviews...</div>
          ) : reviews.length === 0 ? (
            <div className="p-8 text-center text-gray-500">No reviews found</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Customer
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Product
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Rating
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Comment
                    </th>
                    <th className="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Date
                    </th>
                    <th className="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {reviews.map((review) => (
                    <tr key={review.rating_id} className="hover:bg-gray-50 transition">
                      <td className="px-6 py-4 text-sm font-semibold text-gray-900">
                        {review.user?.name || 'Unknown Customer'}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-700">
                        {review.product?.name || 'Unknown Product'}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-1">
                          {renderStars(review.stars)}
                          <span className="text-xs text-gray-600 ml-2">{review.stars}/5</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-600">
                        {truncateComment(review.comment)}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-700 text-right">
                        {formatDate(review.created_at)}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center justify-center gap-2">
                          <button
                            onClick={() => handleViewDetails(review)}
                            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                          >
                            View
                          </button>
                          <button
                            onClick={() =>
                              handleToggleVisibility(
                                review.rating_id,
                                review.is_visible ?? true
                              )
                            }
                            className="p-2 text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                            title={
                              review.is_visible ?? true ? 'Hide review' : 'Show review'
                            }
                          >
                            {review.is_visible ?? true ? (
                              <FiEye size={18} />
                            ) : (
                              <FiEyeOff size={18} />
                            )}
                          </button>
                          <button
                            onClick={() => setDeleteConfirm(review.rating_id)}
                            className="p-2 text-red-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                            title="Delete review"
                          >
                            <FiTrash2 size={18} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Review Details Modal */}
      {showDetailsModal && selectedReview && (
        <div style={{position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0, 0, 0, 0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 50}}>
          <div style={{backgroundColor: 'white', borderRadius: '0.5rem', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)', padding: '1.5rem', maxWidth: '28rem', width: '100%', marginLeft: '1rem', marginRight: '1rem'}}>
            {/* Modal Header with Close Button */}
            <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1.5rem'}}>
              <div>
                <h2 style={{fontSize: '1.25rem', fontWeight: 'bold', color: '#111827', margin: 0}}>Review Details</h2>
                <p style={{fontSize: '0.875rem', color: '#9CA3AF', margin: '0.25rem 0 0 0'}}>Full review information</p>
              </div>
              <button
                onClick={() => setShowDetailsModal(false)}
                style={{color: '#9CA3AF', cursor: 'pointer', fontSize: '1.5rem', border: 'none', background: 'none', padding: 0, lineHeight: 1, marginLeft: '1rem'}}
                onMouseEnter={(e) => (e.currentTarget.style.color = '#374151')}
                onMouseLeave={(e) => (e.currentTarget.style.color = '#9CA3AF')}
              >
                ✕
              </button>
            </div>

            {/* Customer and Date Row */}
            <div style={{display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem', marginBottom: '1.5rem'}}>
              <div>
                <label style={{display: 'block', fontSize: '0.75rem', color: '#9CA3AF', fontWeight: '600', marginBottom: '0.25rem'}}>
                  Customer
                </label>
                <p style={{fontSize: '0.875rem', fontWeight: 'bold', color: '#111827'}}>
                  {selectedReview.user?.name || 'Unknown'}
                </p>
              </div>
              <div>
                <label style={{display: 'block', fontSize: '0.75rem', color: '#9CA3AF', fontWeight: '600', marginBottom: '0.25rem'}}>
                  Date
                </label>
                <p style={{fontSize: '0.875rem', fontWeight: 'bold', color: '#111827'}}>
                  {formatDate(selectedReview.created_at)}
                </p>
              </div>
            </div>

            {/* Product */}
            <div style={{marginBottom: '1.5rem'}}>
              <label style={{display: 'block', fontSize: '0.75rem', color: '#9CA3AF', fontWeight: '600', marginBottom: '0.5rem'}}>
                Product
              </label>
              <p style={{fontSize: '0.875rem', fontWeight: 'bold', color: '#111827'}}>
                {selectedReview.product?.name || 'Unknown'}
              </p>
            </div>

            {/* Rating */}
            <div style={{marginBottom: '1.5rem'}}>
              <label style={{display: 'block', fontSize: '0.75rem', color: '#9CA3AF', fontWeight: '600', marginBottom: '0.5rem'}}>
                Rating
              </label>
              <div style={{display: 'flex', alignItems: 'center', gap: '0.5rem'}}>
                {renderStars(selectedReview.stars)}
                <span style={{fontSize: '0.875rem', fontWeight: '600', color: '#111827'}}>
                  {selectedReview.stars}/5
                </span>
              </div>
            </div>

            {/* Comment */}
            <div>
              <label style={{display: 'block', fontSize: '0.75rem', color: '#9CA3AF', fontWeight: '600', marginBottom: '0.5rem'}}>
                Comment
              </label>
              <div style={{backgroundColor: '#F3F4F6', borderRadius: '0.5rem', padding: '1rem', fontSize: '0.875rem', color: '#1F2937', lineHeight: '1.5'}}>
                {selectedReview.comment}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Delete Confirmation Modal */}
      {deleteConfirm !== null && (
        <div style={{position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0, 0, 0, 0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 50}}>
          <div style={{backgroundColor: 'white', borderRadius: '0.5rem', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)', padding: '1.5rem', maxWidth: '32rem', width: '100%', marginLeft: '1rem', marginRight: '1rem'}}>
            <h3 style={{fontSize: '1.125rem', fontWeight: 'bold', color: '#111827', marginBottom: '0.5rem'}}>Delete Review</h3>
            <p style={{color: '#4B5563', marginBottom: '1.5rem'}}>
              Are you sure you want to delete this review? This action cannot be undone.
            </p>
            <div style={{display: 'flex', gap: '1rem', justifyContent: 'flex-end'}}>
              <button
                onClick={() => setDeleteConfirm(null)}
                style={{padding: '0.5rem 1rem', color: '#374151', border: '1px solid #D1D5DB', borderRadius: '0.5rem', cursor: 'pointer', backgroundColor: 'white', fontSize: '0.875rem'}}
                onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = '#F3F4F6')}
                onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = 'white')}
              >
                Cancel
              </button>
              <button
                onClick={() => handleDeleteReview(deleteConfirm)}
                style={{padding: '0.5rem 1rem', backgroundColor: '#DC2626', color: 'white', borderRadius: '0.5rem', cursor: 'pointer', border: 'none', fontSize: '0.875rem'}}
                onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = '#B91C1C')}
                onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = '#DC2626')}
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
