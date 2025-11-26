import { promises as fs } from 'fs';
import path from 'path';
import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  try {
    const { filename } = await req.json();

    if (!filename) {
      return NextResponse.json(
        { error: 'Filename is required' },
        { status: 400 }
      );
    }

    // Construct the file path
    const filePath = path.join(process.cwd(), 'public', 'profile_pics', filename);

    // Check if file exists
    try {
      await fs.access(filePath);
      // File exists, delete it
      await fs.unlink(filePath);
      return NextResponse.json({ success: true, message: 'File deleted successfully' });
    } catch (error: any) {
      if (error.code === 'ENOENT') {
        // File doesn't exist, return success anyway
        return NextResponse.json({ success: true, message: 'File does not exist' });
      }
      throw error;
    }
  } catch (error: any) {
    console.error('Error deleting profile picture:', error);
    return NextResponse.json(
      { error: 'Failed to delete profile picture', details: error.message },
      { status: 500 }
    );
  }
}
